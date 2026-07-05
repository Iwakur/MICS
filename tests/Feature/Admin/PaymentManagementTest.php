<?php

/**
 * MICS test coverage: tests Feature Admin PaymentManagementTest. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature\Admin;

use App\Enums\ReviewStatus;
use App\Models\Payment;
use App\Models\Student;
use App\Models\StudentMonth;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PaymentManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_creates_edits_and_validates_a_payment_draft(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();

        $this->actingAs($admin)->get(route('admin.payments.index', ['month' => '2026-07']))
            ->assertOk()->assertSee('Student Payments');
        $this->actingAs($admin)->get(route('admin.payments.create', ['month' => '2026-07']))
            ->assertOk()->assertSee('Record Payment Draft');

        $this->actingAs($admin)->post(route('admin.payments.store'), [
            'student_id' => $student->id,
            'month' => '2026-07',
            'paid_at' => '2026-07-20 10:30:00',
            'amount' => 80,
            'payment_method' => 'bank_transfer',
            'note' => 'Bank statement pending review.',
        ])->assertRedirect();

        $payment = Payment::query()->firstOrFail();
        $this->assertSame(ReviewStatus::Draft, $payment->status);
        $this->assertSame('2026-07-01', $payment->studentMonth->month_date->format('Y-m-d'));
        $this->assertSame(0.0, $payment->studentMonth->closingBalance());
        $nextMonth = StudentMonth::factory()->for($student)->create([
            'month_date' => '2026-08-01',
            'opening_balance' => 0,
            'charge_amount' => 20,
        ]);

        $this->actingAs($admin)->put(route('admin.payments.update', $payment), [
            'student_id' => $student->id,
            'month' => '2026-07',
            'paid_at' => '2026-07-20 10:30:00',
            'amount' => 85,
            'payment_method' => 'bank_transfer',
            'note' => 'Matched to bank statement.',
        ])->assertRedirect(route('admin.payments.edit', $payment));

        $this->actingAs($admin)->post(route('admin.payments.validate', $payment))->assertRedirect();

        $payment->refresh();
        $this->assertSame(ReviewStatus::Validated, $payment->status);
        $this->assertSame($admin->id, $payment->validated_by_user_id);
        $this->assertNotNull($payment->validated_at);
        $this->assertSame(-85.0, $payment->studentMonth->closingBalance());
        $this->assertSame('-85.00', $nextMonth->refresh()->opening_balance);
        $this->actingAs($admin)->get(route('admin.payments.edit', $payment))
            ->assertOk()->assertSee('Validated Payment')->assertSee($admin->username);
    }

    public function test_validated_payment_is_immutable_and_teacher_has_no_access(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $payment = Payment::factory()->validated()->create();
        $payload = [
            'student_id' => $payment->studentMonth->student_id,
            'month' => $payment->studentMonth->month_date->format('Y-m'),
            'paid_at' => now()->toDateTimeString(),
            'amount' => 999,
            'payment_method' => 'cash',
        ];

        $this->actingAs($admin)->put(route('admin.payments.update', $payment), $payload)->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.payments.destroy', $payment))->assertForbidden();
        $this->actingAs($teacher)->get(route('admin.payments.index'))->assertForbidden();
    }

    public function test_admin_reverses_validated_payment_once_and_restores_future_balances(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();
        $month = StudentMonth::factory()->for($student)->create([
            'month_date' => '2026-07-01',
            'opening_balance' => 0,
            'charge_amount' => 100,
            'manual_adjustment' => 0,
        ]);
        $payment = Payment::factory()->validated()->for($month)->create(['amount' => 40]);
        $nextMonth = StudentMonth::factory()->for($student)->create([
            'month_date' => '2026-08-01',
            'opening_balance' => 60,
        ]);

        $this->actingAs($admin)->post(route('admin.payments.reverse', $payment), [
            'reason' => 'Payment was assigned to the wrong student.',
        ])->assertRedirect();

        $reversal = Payment::query()->where('reversal_of_payment_id', $payment->id)->firstOrFail();
        $this->assertSame('-40.00', $reversal->amount);
        $this->assertSame(ReviewStatus::Validated, $reversal->status);
        $this->assertSame($admin->id, $reversal->validated_by_user_id);
        $this->assertSame('Payment was assigned to the wrong student.', $reversal->note);
        $this->assertSame(100.0, $month->closingBalance());
        $this->assertSame('100.00', $nextMonth->refresh()->opening_balance);

        $this->actingAs($admin)->get(route('admin.payments.edit', $reversal))
            ->assertOk()->assertSee('Payment Reversal')->assertSee('wrong student');
        $this->actingAs($admin)->post(route('admin.payments.reverse', $payment), [
            'reason' => 'Trying to reverse the same payment again.',
        ])->assertForbidden();
    }

    public function test_reversal_requires_a_meaningful_reason(): void
    {
        $admin = User::factory()->admin()->create();
        $payment = Payment::factory()->validated()->create();

        $this->actingAs($admin)->post(route('admin.payments.reverse', $payment), ['reason' => 'wrong'])
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseMissing('payments', ['reversal_of_payment_id' => $payment->id]);
    }

    public function test_new_payment_month_carries_the_latest_prior_balance(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create();
        StudentMonth::factory()->for($student)->create([
            'month_date' => '2026-06-01',
            'opening_balance' => 20,
            'charge_amount' => 100,
            'manual_adjustment' => 0,
        ]);

        $this->actingAs($admin)->post(route('admin.payments.store'), [
            'student_id' => $student->id,
            'month' => '2026-07',
            'paid_at' => '2026-07-10 10:00:00',
            'amount' => 50,
            'payment_method' => 'cash',
        ])->assertRedirect();

        $july = StudentMonth::query()->whereBelongsTo($student)->whereDate('month_date', '2026-07-01')->firstOrFail();
        $this->assertSame('120.00', $july->opening_balance);
    }
}

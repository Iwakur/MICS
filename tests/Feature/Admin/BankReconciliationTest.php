<?php

namespace Tests\Feature\Admin;

use App\Enums\ReviewStatus;
use App\Models\BankMonth;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class BankReconciliationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_reconciles_expected_and_actual_balance_with_audit_history(): void
    {
        $admin = User::factory()->admin()->create();
        Payment::factory()->validated()->create(['paid_at' => '2026-07-12', 'amount' => 500]);
        Expense::factory()->create(['month_date' => '2026-07-01', 'amount' => 125, 'status' => ReviewStatus::Validated]);

        $this->actingAs($admin)->get(route('admin.bank-months.index', ['month' => '2026-07']))
            ->assertOk()->assertSee('500.00')->assertSee('125.00')->assertSee('375.00');

        $this->actingAs($admin)->post(route('admin.bank-months.store'), [
            'month' => '2026-07',
            'closing_balance' => '380.00',
            'variance_reason' => 'Five euros of bank interest was received.',
        ])->assertRedirect();

        $bankMonth = BankMonth::query()->firstOrFail();
        $this->assertSame('375.00', $bankMonth->expected_closing_balance);
        $this->assertSame('380.00', $bankMonth->closing_balance);
        $this->assertSame('reconciled', $bankMonth->status);
        $this->assertSame($admin->id, $bankMonth->reconciled_by_user_id);
        $this->assertSame('reconciled', $bankMonth->events()->firstOrFail()->action);

        $this->actingAs($admin)->post(route('admin.bank-months.reopen', $bankMonth), [
            'reason' => 'The actual statement closing amount was entered incorrectly.',
        ])->assertRedirect();
        $this->assertSame('draft', $bankMonth->refresh()->status);
        $this->assertSame(['reconciled', 'reopened'], $bankMonth->events()->reorder('occurred_at')->orderBy('id')->pluck('action')->all());
    }

    public function test_variance_requires_reason_and_teacher_has_no_access(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($admin)
            ->from(route('admin.bank-months.index', ['month' => '2026-07']))
            ->post(route('admin.bank-months.store'), [
                'month' => '2026-07', 'closing_balance' => 10,
            ])
            ->assertRedirect(route('admin.bank-months.index', ['month' => '2026-07']))
            ->assertSessionHasErrors('variance_reason')
            ->assertSessionHasInput('closing_balance', 10);

        $this->assertDatabaseEmpty('bank_months');
        $this->actingAs($teacher)->get(route('admin.bank-months.index'))->assertForbidden();
    }

    public function test_older_bank_month_cannot_be_reconciled_or_reopened_beneath_a_later_reconciliation(): void
    {
        $admin = User::factory()->admin()->create();
        $july = BankMonth::factory()->create(['month_date' => '2026-07-01', 'status' => 'reconciled']);
        BankMonth::factory()->create(['month_date' => '2026-08-01', 'status' => 'reconciled']);

        $this->actingAs($admin)->post(route('admin.bank-months.reopen', $july), [
            'reason' => 'Correcting the earlier bank statement balance.',
        ])->assertInvalid('month');

        $july->update(['status' => 'draft']);
        $this->actingAs($admin)->post(route('admin.bank-months.store'), [
            'month' => '2026-07',
            'closing_balance' => 0,
        ])->assertInvalid('month');
    }
}

<?php

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Enums\StaffCompensationMode;
use App\Models\BillingMonth;
use App\Models\Expense;
use App\Models\LessonType;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentMonth;
use App\Models\User;
use App\Services\BankReconciliationService;
use App\Services\MonthClosingService;
use App\Services\StudentBalanceService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class LongitudinalWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_three_month_workflow_preserves_history_and_propagates_corrections(): void
    {
        CarbonImmutable::setTestNow('2026-07-10 09:00:00');
        $admin = User::factory()->admin()->create();
        $teacher = Staff::factory()->create(['compensation_mode' => StaffCompensationMode::Dynamic, 'salary_amount' => null]);
        $lessonType = LessonType::factory()->create(['lesson_price' => 100, 'teacher_share_per_lesson' => 40]);
        $student = Student::factory()->for($teacher, 'teacher')->for($lessonType)->create(['joined_at' => '2026-07-01']);
        StudentMonth::factory()->for($student)->create([
            'month_date' => '2026-07-01', 'lesson_count' => 2,
            'opening_balance' => 0, 'charge_amount' => 0, 'manual_adjustment' => 0,
        ]);

        $closing = app(MonthClosingService::class);
        $closing->close(CarbonImmutable::parse('2026-07-01'), $admin);
        $july = StudentMonth::query()->whereBelongsTo($student)->whereDate('month_date', '2026-07-01')->firstOrFail();
        $this->assertSame('200.00', $july->charge_amount);

        $payment = Payment::factory()->for($july)->create(['paid_at' => '2026-07-20', 'amount' => 150]);
        $payment->update(['status' => ReviewStatus::Validated, 'validated_by_user_id' => $admin->id, 'validated_at' => now()]);
        app(StudentBalanceService::class)->propagateFrom($july);

        $lessonType->update(['lesson_price' => 120]);
        $student->update(['discount_amount' => 20]);
        $this->assertSame('100.00', $lessonType->rateFor(CarbonImmutable::parse('2026-07-01'))->lesson_price);

        CarbonImmutable::setTestNow('2026-08-10 09:00:00');
        StudentMonth::query()->whereBelongsTo($student)->whereDate('month_date', '2026-08-01')->update(['lesson_count' => 1]);
        $closing->close(CarbonImmutable::parse('2026-08-01'), $admin);
        $august = StudentMonth::query()->whereBelongsTo($student)->whereDate('month_date', '2026-08-01')->firstOrFail();
        $this->assertSame('100.00', $august->charge_amount);

        $this->actingAs($admin)->post(route('admin.payments.reverse', $payment), [
            'amount' => 50,
            'reason' => 'A documented partial refund was sent to the student.',
        ])->assertRedirect();
        $this->assertSame(100.0, $july->refresh()->closingBalance());
        $this->assertSame('100.00', $august->refresh()->opening_balance);

        Expense::query()->where('is_auto_generated', true)->whereDate('month_date', '2026-08-01')->update([
            'status' => ReviewStatus::Validated, 'validated_by_user_id' => $admin->id, 'validated_at' => now(),
        ]);
        app(BankReconciliationService::class)->reconcile(CarbonImmutable::parse('2026-08-01'), '-90.00', null, null, $admin);

        CarbonImmutable::setTestNow('2026-09-10 09:00:00');
        StudentMonth::query()->whereBelongsTo($student)->whereDate('month_date', '2026-09-01')->update(['lesson_count' => 2]);
        $closing->close(CarbonImmutable::parse('2026-09-01'), $admin);
        $september = StudentMonth::query()->whereBelongsTo($student)->whereDate('month_date', '2026-09-01')->firstOrFail();
        $september->update(['status' => ReviewStatus::Validated, 'validated_by_user_id' => $admin->id, 'validated_at' => now()]);
        $validatedCharge = $september->charge_amount;

        $closing->reopen(CarbonImmutable::parse('2026-09-01'), $admin, 'Correcting September lesson inputs after review.');
        $closing->close(CarbonImmutable::parse('2026-09-01'), $admin);
        $this->assertSame($validatedCharge, $september->refresh()->charge_amount);
        $this->assertSame(3, BillingMonth::query()->where('status', 'closed')->count());

        CarbonImmutable::setTestNow();
    }
}

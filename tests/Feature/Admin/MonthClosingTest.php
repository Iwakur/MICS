<?php

/**
 * MICS HUB test coverage: tests Feature Admin MonthClosingTest. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature\Admin;

use App\Enums\BillingMonthStatus;
use App\Enums\StaffCompensationMode;
use App\Enums\StudentStatus;
use App\Models\BillingMonth;
use App\Models\BillingMonthEvent;
use App\Models\Expense;
use App\Models\LessonType;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentMonth;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MonthClosingTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_closes_month_and_generates_charges_salary_drafts_and_balances(): void
    {
        $admin = User::factory()->admin()->create();
        $dynamicTeacher = Staff::factory()->create([
            'compensation_mode' => StaffCompensationMode::Dynamic,
            'salary_amount' => null,
        ]);
        $fixedStaff = Staff::factory()->create([
            'compensation_mode' => StaffCompensationMode::Fixed,
            'salary_amount' => 1200,
        ]);
        $lessonType = LessonType::factory()->create([
            'lesson_price' => 35,
            'teacher_share_per_lesson' => 20,
        ]);
        $plan = Plan::factory()->create([
            'plan_price' => 130,
            'teacher_monthly_amount' => 75,
        ]);
        $lessonStudent = Student::factory()->for($dynamicTeacher, 'teacher')->for($lessonType)->create([
            'joined_at' => '2026-07-01',
            'discount_amount' => 10,
        ]);
        $planStudent = Student::factory()->planBased()->for($dynamicTeacher, 'teacher')->create([
            'joined_at' => '2026-07-01',
            'plan_id' => $plan->id,
            'plan_start_at' => '2026-07-20',
        ]);
        $lessonMonth = StudentMonth::factory()->for($lessonStudent)->create([
            'month_date' => '2026-07-01',
            'lesson_count' => 3,
            'opening_balance' => 50,
            'charge_amount' => 0,
        ]);
        Payment::factory()->validated()->for($lessonMonth)->create(['amount' => 40]);

        $this->actingAs($admin)
            ->get(route('admin.month-closing.index', ['month' => '2026-07']))
            ->assertOk()
            ->assertSee('225.00')
            ->assertSee('1,335.00');

        $this->actingAs($admin)
            ->post(route('admin.month-closing.store'), ['month' => '2026-07'])
            ->assertRedirect(route('admin.month-closing.index', ['month' => '2026-07']));

        $this->assertSame('95.00', $lessonMonth->refresh()->charge_amount);
        $this->assertSame('130.00', StudentMonth::query()->whereBelongsTo($planStudent)->whereDate('month_date', '2026-07-01')->firstOrFail()->charge_amount);
        $this->assertSame('105.00', StudentMonth::query()->whereBelongsTo($lessonStudent)->whereDate('month_date', '2026-08-01')->firstOrFail()->opening_balance);

        $dynamicSalary = Expense::query()->where('staff_id', $dynamicTeacher->id)->firstOrFail();
        $fixedSalary = Expense::query()->where('staff_id', $fixedStaff->id)->firstOrFail();
        $this->assertSame('135.00', $dynamicSalary->amount);
        $this->assertSame('1200.00', $fixedSalary->amount);
        $this->assertTrue($dynamicSalary->is_auto_generated);
        $this->assertCount(2, $dynamicSalary->salarySources);
        $this->assertSame(['per_lesson', 'plan_based'], $dynamicSalary->salarySources->pluck('source_type')->sort()->values()->all());

        $billingMonth = BillingMonth::query()->firstOrFail();
        $this->assertSame(BillingMonthStatus::Closed, $billingMonth->status);
        $this->assertSame($admin->id, $billingMonth->closed_by_user_id);
        $this->assertNotNull($billingMonth->closed_at);
    }

    public function test_closed_month_cannot_generate_duplicate_drafts(): void
    {
        $admin = User::factory()->admin()->create();
        Staff::factory()->create(['salary_amount' => 900]);

        $this->actingAs($admin)->post(route('admin.month-closing.store'), ['month' => '2026-07'])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.month-closing.store'), ['month' => '2026-07'])->assertUnprocessable();

        $this->assertSame(1, Expense::query()->where('is_auto_generated', true)->count());
    }

    public function test_teacher_cannot_preview_or_close_a_month(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)->get(route('admin.month-closing.index'))->assertForbidden();
        $this->actingAs($teacher)->post(route('admin.month-closing.store'), ['month' => '2026-07'])->assertForbidden();
    }

    public function test_admin_reopens_month_with_audit_reason_and_reclose_preserves_validated_records(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = Staff::factory()->create(['salary_amount' => 900]);
        $student = Student::factory()->create(['joined_at' => '2026-07-01']);
        $month = StudentMonth::factory()->for($student)->create([
            'month_date' => '2026-07-01',
            'charge_amount' => 321,
            'status' => 'validated',
        ]);

        $this->actingAs($admin)->post(route('admin.month-closing.store'), ['month' => '2026-07'])->assertRedirect();
        $salary = Expense::query()->where('staff_id', $staff->id)->firstOrFail();
        $salary->update(['amount' => 975, 'status' => 'validated']);

        $this->actingAs($admin)->post(route('admin.month-closing.reopen'), [
            'month' => '2026-07',
            'reason' => 'Correcting late lesson activity.',
        ])->assertRedirect();

        $billingMonth = BillingMonth::query()->firstOrFail();
        $this->assertSame(BillingMonthStatus::Open, $billingMonth->status);
        $this->assertDatabaseHas('billing_month_events', [
            'billing_month_id' => $billingMonth->id,
            'user_id' => $admin->id,
            'action' => 'reopened',
            'reason' => 'Correcting late lesson activity.',
        ]);

        $this->actingAs($admin)->post(route('admin.month-closing.store'), ['month' => '2026-07'])->assertRedirect();

        $this->assertSame('321.00', $month->refresh()->charge_amount);
        $this->assertSame('975.00', $salary->refresh()->amount);
        $this->assertSame(3, BillingMonthEvent::query()->count());
        $this->actingAs($admin)->get(route('admin.month-closing.index', ['month' => '2026-07']))
            ->assertOk()->assertSee('Lifecycle Audit')->assertSee('Correcting late lesson activity.');
    }

    public function test_closing_carries_paused_student_debt_without_generating_a_new_charge(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create([
            'status' => StudentStatus::Paused,
            'joined_at' => '2026-06-01',
        ]);
        StudentMonth::factory()->for($student)->create([
            'month_date' => '2026-07-01',
            'opening_balance' => 70,
            'charge_amount' => 0,
            'manual_adjustment' => 0,
        ]);

        $this->actingAs($admin)->post(route('admin.month-closing.store'), ['month' => '2026-07'])
            ->assertRedirect();

        $august = StudentMonth::query()->whereBelongsTo($student)
            ->whereDate('month_date', '2026-08-01')->firstOrFail();

        $this->assertSame('70.00', $august->opening_balance);
        $this->assertSame('0.00', $august->charge_amount);
    }
}

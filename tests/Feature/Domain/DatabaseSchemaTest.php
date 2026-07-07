<?php

/**
 * MICS HUB test coverage: tests Feature Domain DatabaseSchemaTest. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature\Domain;

use App\Enums\ReviewStatus;
use App\Enums\StaffCompensationMode;
use App\Enums\StudentBillingType;
use App\Models\BankMonth;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\LessonType;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\Student;
use App\Models\StudentMonth;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_domain_records_are_connected_through_eloquent_relationships(): void
    {
        $role = StaffRole::factory()->create();
        $teacher = Staff::factory()->for($role, 'role')->create();
        $user = User::factory()->for($teacher, 'staffMember')->create();
        $lessonType = LessonType::factory()->create();
        $student = Student::factory()
            ->for($teacher, 'teacher')
            ->for($lessonType)
            ->create();
        $month = StudentMonth::factory()->for($student)->create();
        $payment = Payment::factory()->for($month)->validated()->create();
        $category = ExpenseCategory::factory()->create();
        $expense = Expense::factory()
            ->for($teacher, 'staffMember')
            ->for($category, 'category')
            ->validated()
            ->create();

        $this->assertTrue($role->staffMembers->contains($teacher));
        $this->assertTrue($teacher->user->is($user));
        $this->assertTrue($teacher->students->contains($student));
        $this->assertTrue($student->teacher->is($teacher));
        $this->assertTrue($lessonType->students->contains($student));
        $this->assertTrue($student->months->contains($month));
        $this->assertTrue($month->payments->contains($payment));
        $this->assertTrue($category->expenses->contains($expense));
        $this->assertTrue($teacher->expenses->contains($expense));
        $this->assertSame(StaffCompensationMode::Fixed, $teacher->compensation_mode);
        $this->assertSame(ReviewStatus::Validated, $payment->status);
        $this->assertSame(ReviewStatus::Validated, $expense->status);
    }

    public function test_plan_based_student_uses_the_plan_relationship(): void
    {
        $plan = Plan::factory()->create();
        $student = Student::factory()->planBased()->create(['plan_id' => $plan->id]);

        $this->assertSame(StudentBillingType::PlanBased, $student->billing_type);
        $this->assertTrue($student->plan->is($plan));
        $this->assertNull($student->lessonType);
        $this->assertNull($student->lesson_amount);
    }

    public function test_one_student_can_have_only_one_row_for_a_month(): void
    {
        $month = StudentMonth::factory()->create(['month_date' => '2026-07-01']);

        $this->expectException(QueryException::class);

        StudentMonth::factory()->create([
            'student_id' => $month->student_id,
            'month_date' => '2026-07-01',
        ]);
    }

    public function test_monthly_balance_counts_only_validated_payments(): void
    {
        $month = StudentMonth::factory()->create([
            'opening_balance' => 100,
            'charge_amount' => 80,
            'manual_adjustment' => -10,
        ]);

        Payment::factory()->for($month)->create(['amount' => 100]);
        Payment::factory()->for($month)->validated()->create(['amount' => 45.25]);

        $this->assertSame(124.75, $month->closingBalance());
        $this->assertCount(1, $month->validatedPayments);
    }

    public function test_deleting_a_student_cascades_months_and_payments(): void
    {
        $student = Student::factory()->create();
        $month = StudentMonth::factory()->for($student)->create();
        $payment = Payment::factory()->for($month)->create();

        $student->delete();

        $this->assertModelMissing($month);
        $this->assertModelMissing($payment);
    }

    public function test_deleting_optional_staff_links_preserves_financial_history(): void
    {
        $staffMember = Staff::factory()->create();
        $user = User::factory()->for($staffMember, 'staffMember')->create();
        $expense = Expense::factory()->for($staffMember, 'staffMember')->create();

        $staffMember->delete();

        $this->assertNull($user->refresh()->staff_id);
        $this->assertNull($expense->refresh()->staff_id);
    }

    public function test_catalog_and_month_identifiers_are_unique(): void
    {
        StaffRole::factory()->create(['name' => 'Teacher']);
        BankMonth::factory()->create(['month_date' => '2026-07-01']);

        $this->expectException(QueryException::class);

        BankMonth::factory()->create(['month_date' => '2026-07-01']);
    }
}

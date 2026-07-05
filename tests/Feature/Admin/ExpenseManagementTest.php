<?php

/**
 * MICS test coverage: tests Feature Admin ExpenseManagementTest. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature\Admin;

use App\Enums\ReviewStatus;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ExpenseManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_create_edit_and_validate_a_manual_expense(): void
    {
        $admin = User::factory()->admin()->create();
        $category = ExpenseCategory::factory()->create();

        $this->actingAs($admin)->post(route('admin.expenses.store'), [
            'expense_category_id' => $category->id, 'staff_id' => null, 'month_date' => '2026-07-01',
            'amount' => 125, 'status' => 'draft', 'note' => 'Printer repair',
        ])->assertRedirect(route('admin.expenses.index'));

        $expense = Expense::query()->firstOrFail();
        $this->assertFalse($expense->is_auto_generated);

        $this->actingAs($admin)->put(route('admin.expenses.update', $expense), [
            'expense_category_id' => $category->id, 'staff_id' => null, 'month_date' => '2026-07-01',
            'amount' => 135, 'status' => 'validated', 'note' => 'Final invoice',
        ])->assertRedirect(route('admin.expenses.index'));

        $this->assertSame(ReviewStatus::Validated, $expense->refresh()->status);
        $this->assertSame('135.00', $expense->amount);
        $this->assertSame($admin->id, $expense->validated_by_user_id);
        $this->assertNotNull($expense->validated_at);
        $this->actingAs($admin)->put(route('admin.expenses.update', $expense), [])->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.expenses.destroy', $expense))->assertForbidden();
    }

    public function test_admin_can_correct_and_validate_a_generated_salary_but_not_delete_it(): void
    {
        $admin = User::factory()->admin()->create();
        $category = ExpenseCategory::factory()->create();
        $staff = Staff::factory()->create();
        $salary = Expense::factory()->for($staff, 'staffMember')->for($category, 'category')->create([
            'amount' => 900, 'is_auto_generated' => true, 'generation_key' => 'salary:2026-07:staff:'.$staff->id,
        ]);

        $this->actingAs($admin)->put(route('admin.expenses.update', $salary), [
            'expense_category_id' => $category->id, 'staff_id' => $staff->id, 'month_date' => '2026-07-01',
            'amount' => 925, 'status' => 'validated', 'note' => 'Approved correction',
        ])->assertRedirect(route('admin.expenses.index'));

        $this->assertSame('925.00', $salary->refresh()->amount);
        $this->assertSame(ReviewStatus::Validated, $salary->status);
        $this->assertSame($admin->id, $salary->validated_by_user_id);
        $this->actingAs($admin)->delete(route('admin.expenses.destroy', $salary))->assertForbidden();
    }

    public function test_generated_salary_correction_requires_an_explanation(): void
    {
        $admin = User::factory()->admin()->create();
        $category = ExpenseCategory::factory()->create();
        $staff = Staff::factory()->create();
        $salary = Expense::factory()->for($staff, 'staffMember')->for($category, 'category')->create([
            'amount' => 900,
            'is_auto_generated' => true,
            'generation_key' => 'salary:2026-07:staff:'.$staff->id,
        ]);
        $salary->salarySources()->create([
            'source_type' => 'fixed',
            'description' => 'Fixed salary',
            'units' => 1,
            'rate' => 900,
            'amount' => 900,
        ]);

        $this->actingAs($admin)->put(route('admin.expenses.update', $salary), [
            'expense_category_id' => $category->id,
            'staff_id' => $staff->id,
            'month_date' => '2026-07-01',
            'amount' => 925,
            'status' => 'draft',
            'note' => '',
        ])->assertInvalid('note');

        $this->assertSame('900.00', $salary->fresh()->amount);
    }
}

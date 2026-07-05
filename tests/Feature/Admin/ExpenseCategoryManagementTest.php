<?php

namespace Tests\Feature\Admin;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ExpenseCategoryManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_manages_categories_and_used_category_is_archived(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->post(route('admin.expense-categories.store'), [
            'name' => 'Insurance', 'note' => 'Annual policies', 'is_active' => '1',
        ])->assertRedirect();

        $category = ExpenseCategory::query()->where('name', 'Insurance')->firstOrFail();
        Expense::factory()->for($category, 'category')->create();
        $this->actingAs($admin)->delete(route('admin.expense-categories.destroy', $category))->assertRedirect();

        $this->assertFalse($category->refresh()->is_active);
        $this->assertModelExists($category);
    }

    public function test_unused_category_is_deleted_and_teacher_is_denied(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();
        $category = ExpenseCategory::factory()->create();

        $this->actingAs($admin)->delete(route('admin.expense-categories.destroy', $category))->assertRedirect();
        $this->assertModelMissing($category);
        $this->actingAs($teacher)->get(route('admin.expense-categories.index'))->assertForbidden();
    }
}

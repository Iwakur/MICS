<?php

/**
 * MICS source: database seeders ReferenceDataSeeder. See docs/file-reference.md for its full responsibility.
 */

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\LessonType;
use App\Models\Plan;
use App\Models\StaffRole;
use Illuminate\Database\Seeder;

class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->staffRoles() as $role) {
            StaffRole::query()->updateOrCreate(['name' => $role['name']], $role);
        }

        foreach ($this->lessonTypes() as $lessonType) {
            LessonType::query()->updateOrCreate(['name' => $lessonType['name']], $lessonType);
        }

        foreach ($this->plans() as $plan) {
            Plan::query()->updateOrCreate(['name' => $plan['name']], $plan);
        }

        foreach ($this->expenseCategories() as $category) {
            ExpenseCategory::query()->updateOrCreate(['name' => $category['name']], $category);
        }
    }

    private function staffRoles(): array
    {
        return [
            ['name' => 'Teacher', 'can_teach' => true, 'is_active' => true, 'note' => 'Standard teaching role.'],
            ['name' => 'Manager', 'can_teach' => false, 'is_active' => true, 'note' => 'School operations and administration.'],
            ['name' => 'Assistant', 'can_teach' => false, 'is_active' => true, 'note' => 'Operational support role.'],
        ];
    }

    private function lessonTypes(): array
    {
        return [
            ['name' => 'Standard 45', 'duration_minutes' => 45, 'lesson_price' => 35, 'teacher_share_per_lesson' => 20, 'is_assignable' => true, 'note' => 'Standard individual lesson.'],
            ['name' => 'Extended 60', 'duration_minutes' => 60, 'lesson_price' => 45, 'teacher_share_per_lesson' => 27, 'is_assignable' => true, 'note' => 'Extended individual lesson.'],
            ['name' => 'Intensive 90', 'duration_minutes' => 90, 'lesson_price' => 65, 'teacher_share_per_lesson' => 40, 'is_assignable' => true, 'note' => 'Long intensive lesson.'],
        ];
    }

    private function plans(): array
    {
        return [
            ['name' => 'Starter Monthly', 'duration_minutes' => 45, 'lesson_count' => 4, 'lesson_price' => 35, 'plan_price' => 130, 'teacher_monthly_amount' => 75, 'is_assignable' => true, 'note' => 'Four lessons per month.'],
            ['name' => 'Standard Monthly', 'duration_minutes' => 60, 'lesson_count' => 8, 'lesson_price' => 45, 'plan_price' => 330, 'teacher_monthly_amount' => 195, 'is_assignable' => true, 'note' => 'Eight lessons per month.'],
            ['name' => 'Intensive Monthly', 'duration_minutes' => 90, 'lesson_count' => 8, 'lesson_price' => 65, 'plan_price' => 480, 'teacher_monthly_amount' => 290, 'is_assignable' => true, 'note' => 'Eight intensive lessons per month.'],
        ];
    }

    private function expenseCategories(): array
    {
        return [
            ['name' => 'Salary', 'note' => 'Generated staff salary drafts.'],
            ['name' => 'Rent', 'note' => 'Premises and classroom rent.'],
            ['name' => 'Utilities', 'note' => 'Electricity, internet, and other utilities.'],
            ['name' => 'Supplies', 'note' => 'Teaching and office supplies.'],
        ];
    }
}

<?php

/**
 * MICS HUB source: database seeders ReferenceDataSeeder. See docs/file-reference.md for its full responsibility.
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
            ['name' => 'Викладач', 'can_teach' => true, 'is_active' => true, 'note' => 'Викладання та ведення призначених учнів.'],
        ];
    }

    private function lessonTypes(): array
    {
        return [
            ['name' => 'Індивідуальний урок 300 ₴', 'duration_minutes' => 60, 'lesson_price' => 300, 'teacher_share_per_lesson' => 150, 'is_assignable' => true, 'note' => 'Тариф із джерела «Травень26».'],
            ['name' => 'Індивідуальний урок 350 ₴', 'duration_minutes' => 60, 'lesson_price' => 350, 'teacher_share_per_lesson' => 175, 'is_assignable' => true, 'note' => 'Тариф із джерела «Травень26».'],
        ];
    }

    private function plans(): array
    {
        return [
            ['name' => 'Базовий', 'duration_minutes' => 60, 'lesson_count' => 9, 'lesson_price' => 438.89, 'plan_price' => 3950, 'teacher_monthly_amount' => 1750, 'is_assignable' => true, 'note' => 'Повна місячна ціна з каталогу «Травень26».'],
            ['name' => 'Прогресивний', 'duration_minutes' => 60, 'lesson_count' => 13, 'lesson_price' => 430.77, 'plan_price' => 5600, 'teacher_monthly_amount' => 2300, 'is_assignable' => true, 'note' => 'Повна місячна ціна з каталогу «Травень26».'],
            ['name' => 'Базовий-350', 'duration_minutes' => 60, 'lesson_count' => 9, 'lesson_price' => 340, 'plan_price' => 3060, 'teacher_monthly_amount' => 1530, 'is_assignable' => true, 'note' => 'Повна місячна ціна з каталогу «Травень26».'],
            ['name' => 'Прогресивний-350', 'duration_minutes' => 60, 'lesson_count' => 13, 'lesson_price' => 353.85, 'plan_price' => 4600, 'teacher_monthly_amount' => 2300, 'is_assignable' => true, 'note' => 'Повна місячна ціна з каталогу «Травень26».'],
            ['name' => 'Базовий-300', 'duration_minutes' => 60, 'lesson_count' => 9, 'lesson_price' => 291.67, 'plan_price' => 2625, 'teacher_monthly_amount' => 1530, 'is_assignable' => true, 'note' => 'Повна місячна ціна з каталогу «Травень26».'],
            ['name' => 'Прогресивний-300', 'duration_minutes' => 60, 'lesson_count' => 13, 'lesson_price' => 303.85, 'plan_price' => 3950, 'teacher_monthly_amount' => 2300, 'is_assignable' => true, 'note' => 'Повна місячна ціна з каталогу «Травень26».'],
            ['name' => 'Стартовий-45', 'duration_minutes' => 45, 'lesson_count' => 4.5, 'lesson_price' => 243.75, 'plan_price' => 975, 'teacher_monthly_amount' => 570, 'is_assignable' => true, 'note' => 'Повна місячна ціна з каталогу «Травень26».'],
            ['name' => 'Стартовий-30', 'duration_minutes' => 30, 'lesson_count' => 4.5, 'lesson_price' => 162.50, 'plan_price' => 650, 'teacher_monthly_amount' => 380, 'is_assignable' => true, 'note' => 'Повна місячна ціна з каталогу «Травень26».'],
        ];
    }

    private function expenseCategories(): array
    {
        return [
            ['name' => 'Зарплата', 'note' => 'Автоматично сформовані чернетки зарплат.'],
            ['name' => 'Реклама', 'note' => 'Реклама, зокрема витрати Facebook.'],
            ['name' => 'Податки', 'note' => 'Податкові платежі.'],
            ['name' => 'CRM', 'note' => 'Сервіси керування клієнтами, зокрема Keepin CRM.'],
            ['name' => 'Відеомонтаж', 'note' => 'Сервіси створення та монтажу відео, зокрема Canvas.'],
        ];
    }
}

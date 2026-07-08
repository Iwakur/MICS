<?php

/**
 * MICS HUB test coverage: database seeding behavior. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature\Infrastructure;

use App\Models\ExpenseCategory;
use App\Models\LessonType;
use App\Models\Plan;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_database_seeder_creates_reviewed_school_data_idempotently(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, StaffRole::query()->count());
        $this->assertSame(2, LessonType::query()->count());
        $this->assertSame(8, Plan::query()->count());
        $this->assertSame(5, ExpenseCategory::query()->count());
        $this->assertSame(2, Staff::query()->count());
        $this->assertSame(2, User::query()->count());
        $this->assertSame(12, Student::query()->count());

        $teacher = User::query()->where('username', 'teacher')->firstOrFail();
        $this->assertTrue($teacher->staffMember->role->can_teach);
        $this->assertSame('Максим', $teacher->staffMember->first_name);
        $this->assertSame(12, $teacher->staffMember->students()->count());
        $this->assertSame(12, $teacher->staffMember->students()->whereHas('configurations')->count());
        $this->assertSame(2, User::query()->whereIn('username', ['admin', 'teacher'])->count());
        $this->assertSame(0, User::query()->whereNotIn('username', ['admin', 'teacher'])->count());
        $this->assertDatabaseHas('plans', ['name' => 'Базовий', 'plan_price' => 3950]);
        $this->assertDatabaseHas('expense_categories', ['name' => 'Зарплата']);
        $student = Student::query()->where('first_name', 'Олексій Кравченко')->firstOrFail();
        $this->assertSame('2026-04-01', $student->joined_at?->format('Y-m-d'));
        $this->assertSame('0.00', $student->discount_amount);

        $this->assertSame('Гузьо', $teacher->staffMember->family_name);
        $this->assertSame('Степанович', $teacher->staffMember->father_name);

        $starterPlan = Plan::query()->where('name', 'Стартовий-45')->firstOrFail();
        $this->assertSame('4.5', $starterPlan->lesson_count);

        $planStudent = Student::query()->where('first_name', 'Паша Семініхін')->firstOrFail();
        $this->assertSame('2026-04-01', $planStudent->joined_at?->format('Y-m-d'));
        $this->assertSame('2026-04-01', $planStudent->plan_start_at?->format('Y-m-d'));
    }
}

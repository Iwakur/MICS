<?php

/**
 * MICS HUB test coverage: application workflow rendering. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature\Workflows;

use App\Models\LessonType;
use App\Models\Plan;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ApplicationWorkflowSmokeTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_all_admin_workflow_pages_render_with_seeded_school_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $routes = [
            route('admin.dashboard'),
            route('admin.users.index'),
            route('admin.users.create'),
            route('admin.users.edit', User::query()->where('username', 'teacher')->firstOrFail()),
            route('admin.staff.index'),
            route('admin.staff.create'),
            route('admin.staff.edit', Staff::query()->firstOrFail()),
            route('admin.staff-roles.index'),
            route('admin.staff-roles.create'),
            route('admin.staff-roles.edit', StaffRole::query()->firstOrFail()),
            route('admin.students.index'),
            route('admin.students.create'),
            route('admin.students.edit', Student::query()->firstOrFail()),
            route('admin.lesson-types.index'),
            route('admin.lesson-types.create'),
            route('admin.lesson-types.edit', LessonType::query()->firstOrFail()),
            route('admin.plans.index'),
            route('admin.plans.create'),
            route('admin.plans.edit', Plan::query()->firstOrFail()),
            route('admin.lesson-counts.index', ['month' => '2026-07']),
            route('admin.month-closing.index', ['month' => '2026-07']),
            route('admin.student-charges.index', ['month' => '2026-07']),
            route('admin.payments.index', ['month' => '2026-07']),
            route('admin.payments.create', ['month' => '2026-07']),
            route('admin.expenses.index', ['month' => '2026-07']),
            route('admin.expenses.create'),
            route('admin.expense-categories.index'),
            route('admin.expense-categories.create'),
            route('admin.bank-months.index', ['month' => '2026-07']),
            route('admin.finance-summary', ['month' => '2026-07']),
        ];

        foreach ($routes as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_all_teacher_workflow_pages_render_with_scoped_seeded_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $teacher = User::query()->where('username', 'teacher')->firstOrFail();
        $student = Student::query()->where('staff_id', $teacher->staff_id)->firstOrFail();
        $routes = [
            route('teacher.dashboard'),
            route('teacher.students.index'),
            route('teacher.students.create'),
            route('teacher.students.edit', $student),
            route('teacher.lesson-counts.index', ['month' => '2026-07']),
        ];

        foreach ($routes as $url) {
            $this->actingAs($teacher)->get($url)->assertOk();
        }
    }
}

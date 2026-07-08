<?php

/**
 * MICS HUB test coverage: monthly lesson-count behavior. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature\LessonCounts;

use App\Enums\BillingMonthStatus;
use App\Models\BillingMonth;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentMonth;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MonthlyLessonCountTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_view_and_save_counts_for_all_per_lesson_students(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create(['joined_at' => '2026-07-10']);

        $this->actingAs($admin)
            ->get(route('admin.lesson-counts.index', ['month' => '2026-07']))
            ->assertOk()
            ->assertSee($student->first_name);

        $this->actingAs($admin)
            ->put(route('admin.lesson-counts.update'), ['month' => '2026-07', 'counts' => [$student->id => 8]])
            ->assertRedirect(route('admin.lesson-counts.index', ['month' => '2026-07']));

        $month = StudentMonth::query()->whereBelongsTo($student)->firstOrFail();
        $this->assertSame(8, $month->lesson_count);
        $this->assertSame('2026-07-01', $month->month_date->toDateString());
        $this->assertSame(BillingMonthStatus::Open, BillingMonth::query()->firstOrFail()->status);
    }

    public function test_teacher_only_sees_and_updates_assigned_students(): void
    {
        $staff = Staff::factory()->create();
        $teacher = User::factory()->teacher()->for($staff, 'staffMember')->create();
        $ownStudent = Student::factory()->for($staff, 'teacher')->create(['joined_at' => '2026-07-01']);
        $otherStudent = Student::factory()->create(['joined_at' => '2026-07-01']);

        $this->actingAs($teacher)
            ->get(route('teacher.lesson-counts.index', ['month' => '2026-07']))
            ->assertOk()
            ->assertSee($ownStudent->first_name)
            ->assertDontSee($otherStudent->first_name);

        $this->actingAs($teacher)
            ->put(route('teacher.lesson-counts.update'), ['month' => '2026-07', 'counts' => [$otherStudent->id => 4]])
            ->assertInvalid('counts');

        $this->assertDatabaseMissing('student_months', ['student_id' => $otherStudent->id]);
    }

    public function test_plan_students_and_students_who_join_later_are_not_available(): void
    {
        $admin = User::factory()->admin()->create();
        $planStudent = Student::factory()->planBased()->create(['joined_at' => '2026-07-01']);
        $futureStudent = Student::factory()->create(['joined_at' => '2026-08-01']);

        $this->actingAs($admin)
            ->get(route('admin.lesson-counts.index', ['month' => '2026-07']))
            ->assertOk()
            ->assertDontSee($planStudent->first_name)
            ->assertDontSee($futureStudent->first_name);
    }

    public function test_closed_month_locks_lesson_counts(): void
    {
        $admin = User::factory()->admin()->create();
        $student = Student::factory()->create(['joined_at' => '2026-07-01']);
        BillingMonth::factory()->create([
            'month_date' => '2026-07-01',
            'status' => BillingMonthStatus::Closed,
            'closed_by_user_id' => $admin->id,
            'closed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->put(route('admin.lesson-counts.update'), ['month' => '2026-07', 'counts' => [$student->id => 5]])
            ->assertInvalid('month');

        $this->assertDatabaseMissing('student_months', ['student_id' => $student->id]);
    }
}

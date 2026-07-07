<?php

namespace Tests\Feature;

use App\Enums\StudentStatus;
use App\Models\Student;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminDashboardStatisticsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dashboard_uses_the_student_configuration_effective_for_the_selected_month(): void
    {
        CarbonImmutable::setTestNow('2026-01-15 12:00:00');
        $admin = User::factory()->admin()->create();
        $perLessonStudent = Student::factory()->create(['joined_at' => '2026-01-10']);
        Student::factory()->planBased()->create(['joined_at' => '2026-01-10']);
        Student::factory()->create([
            'joined_at' => '2026-01-10',
            'status' => StudentStatus::Archived,
        ]);
        Student::factory()->create(['joined_at' => '2026-03-01']);

        $februaryConfiguration = $perLessonStudent->configurations()->firstOrFail()->replicate();
        $februaryConfiguration->effective_from = '2026-02-01';
        $februaryConfiguration->status = StudentStatus::Paused;
        $februaryConfiguration->save();

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['month' => '2026-01']))
            ->assertOk()
            ->assertViewHas('studentStatistics', [
                'total' => 3,
                'active' => 2,
                'paused' => 0,
                'archived' => 1,
                'per_lesson' => 2,
                'plan_based' => 1,
            ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['month' => '2026-02']))
            ->assertOk()
            ->assertViewHas('studentStatistics', [
                'total' => 3,
                'active' => 1,
                'paused' => 1,
                'archived' => 1,
                'per_lesson' => 2,
                'plan_based' => 1,
            ]);
    }

    public function test_dashboard_rejects_an_invalid_month(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['month' => '2026-13']))
            ->assertNotFound();
    }
}

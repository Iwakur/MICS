<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_is_redirected_to_the_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_teacher_is_redirected_to_the_teacher_dashboard(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->get(route('dashboard'));

        $response->assertRedirect(route('teacher.dashboard'));
    }

    public function test_teacher_cannot_access_admin_pages(): void
    {
        $teacher = User::factory()->teacher()->create();

        $dashboardResponse = $this->actingAs($teacher)->get(route('admin.dashboard'));
        $usersResponse = $this->actingAs($teacher)->get(route('admin.users.index'));

        $dashboardResponse->assertForbidden();
        $usersResponse->assertForbidden();
    }
}

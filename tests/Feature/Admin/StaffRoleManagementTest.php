<?php

/**
 * MICS test coverage: tests Feature Admin StaffRoleManagementTest. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature\Admin;

use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StaffRoleManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_default_teacher_role_is_seeded_idempotently(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $role = StaffRole::query()->where('name', 'Teacher')->firstOrFail();

        $this->assertTrue($role->can_teach);
        $this->assertTrue($role->is_active);
        $this->assertSame(1, StaffRole::query()->where('name', 'Teacher')->count());
    }

    public function test_admin_can_create_and_update_a_staff_role(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.staff-roles.store'), [
            'name' => 'Assistant',
            'can_teach' => '0',
            'is_active' => '1',
            'note' => 'Operations support',
        ])->assertRedirect(route('admin.staff-roles.index'));

        $role = StaffRole::query()->where('name', 'Assistant')->firstOrFail();
        $this->assertFalse($role->can_teach);

        $this->actingAs($admin)->put(route('admin.staff-roles.update', $role), [
            'name' => 'Teaching Assistant',
            'can_teach' => '1',
            'is_active' => '1',
            'note' => null,
        ])->assertRedirect(route('admin.staff-roles.index'));

        $this->assertTrue($role->refresh()->can_teach);
        $this->assertSame('Teaching Assistant', $role->name);
    }

    public function test_archiving_a_role_preserves_assigned_staff(): void
    {
        $admin = User::factory()->admin()->create();
        $role = StaffRole::factory()->create();
        $staff = Staff::factory()->for($role, 'role')->create();

        $this->actingAs($admin)
            ->delete(route('admin.staff-roles.destroy', $role))
            ->assertRedirect(route('admin.staff-roles.index'));

        $this->assertFalse($role->refresh()->is_active);
        $this->assertTrue($staff->refresh()->role->is($role));
    }

    public function test_teacher_cannot_manage_staff_roles(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)->get(route('admin.staff-roles.index'))->assertForbidden();
    }
}

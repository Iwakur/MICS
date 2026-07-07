<?php

/**
 * MICS HUB test coverage: tests Feature Admin StaffManagementTest. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature\Admin;

use App\Enums\StaffCompensationMode;
use App\Enums\UserRole;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_create_a_staff_member_and_link_an_existing_user(): void
    {
        $admin = User::factory()->admin()->create();
        $teacherAccount = User::factory()->teacher()->inactive()->create(['staff_id' => null]);
        $role = StaffRole::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.staff.store'), [
            'staff_role_id' => $role->id,
            'first_name' => 'Amina',
            'family_name' => 'Khan',
            'father_name' => 'Rahim',
            'email' => 'amina@example.com',
            'phone' => '+1 555 000 111',
            'birthday' => '1990-01-15',
            'city' => 'Brussels',
            'payout_card_number' => '1234567890123456',
            'compensation_mode' => StaffCompensationMode::Fixed->value,
            'salary_amount' => '1500.00',
            'is_active' => '1',
            'note' => 'New teacher',
            'user_id' => $teacherAccount->id,
        ]);

        $response->assertRedirect(route('admin.staff.index'));

        $staff = Staff::query()->where('email', 'amina@example.com')->first();

        $this->assertNotNull($staff);
        $this->assertTrue($staff->user->is($teacherAccount));
        $this->assertSame(UserRole::Teacher, $staff->user->role);
    }

    public function test_admin_can_replace_a_staff_members_linked_user(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = Staff::factory()->create();
        $user = User::factory()->for($staff, 'staffMember')->create();
        $replacementUser = User::factory()->teacher()->inactive()->create(['staff_id' => null]);
        $role = StaffRole::factory()->create();

        $response = $this->actingAs($admin)->put(route('admin.staff.update', $staff), [
            'staff_role_id' => $role->id,
            'first_name' => 'Updated',
            'family_name' => 'Name',
            'father_name' => 'Father',
            'email' => 'updated@example.com',
            'phone' => '555-222',
            'birthday' => '1988-03-01',
            'city' => 'Antwerp',
            'payout_card_number' => '9999999999999999',
            'compensation_mode' => StaffCompensationMode::Dynamic->value,
            'salary_amount' => '',
            'is_active' => '1',
            'note' => 'Updated staff',
            'user_id' => $replacementUser->id,
        ]);

        $response->assertRedirect(route('admin.staff.index'));

        $staff->refresh();
        $user->refresh();

        $this->assertSame('Updated', $staff->first_name);
        $this->assertSame(StaffCompensationMode::Dynamic, $staff->compensation_mode);
        $this->assertNull($staff->salary_amount);
        $this->assertNull($user->staff_id);
        $this->assertSame($staff->id, $replacementUser->refresh()->staff_id);
    }

    public function test_admin_cannot_link_an_account_owned_by_another_staff_member(): void
    {
        $admin = User::factory()->admin()->create();
        $existingStaff = Staff::factory()->create();
        $linkedUser = User::factory()->for($existingStaff, 'staffMember')->create();
        $role = StaffRole::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.staff.store'), [
            'staff_role_id' => $role->id,
            'first_name' => 'Second',
            'compensation_mode' => StaffCompensationMode::Fixed->value,
            'salary_amount' => '1200',
            'is_active' => '1',
            'user_id' => $linkedUser->id,
        ]);

        $response->assertInvalid('user_id');
        $this->assertDatabaseMissing('staff', ['first_name' => 'Second']);
    }

    public function test_admin_can_archive_staff(): void
    {
        $admin = User::factory()->admin()->create();
        $staff = Staff::factory()->create(['is_active' => true]);
        $linkedUser = User::factory()->for($staff, 'staffMember')->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.staff.index'))
            ->delete(route('admin.staff.destroy', $staff));

        $response->assertRedirect(route('admin.staff.index'));

        $this->assertFalse($staff->refresh()->is_active);
        $this->assertFalse($linkedUser->refresh()->is_active);
    }

    public function test_fixed_compensation_requires_a_salary_amount(): void
    {
        $admin = User::factory()->admin()->create();
        $role = StaffRole::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.staff.store'), [
            'staff_role_id' => $role->id,
            'first_name' => 'Fixed Staff',
            'compensation_mode' => StaffCompensationMode::Fixed->value,
            'salary_amount' => '',
            'is_active' => '1',
        ]);

        $response->assertInvalid('salary_amount');
    }

    public function test_teacher_cannot_access_staff_pages(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->get(route('admin.staff.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_open_staff_edit_with_current_inactive_role_and_linked_user(): void
    {
        $admin = User::factory()->admin()->create();
        $role = StaffRole::factory()->create(['is_active' => false]);
        $staff = Staff::factory()->for($role, 'role')->create();
        $linkedUser = User::factory()->for($staff, 'staffMember')->create();

        $this->actingAs($admin)
            ->get(route('admin.staff.edit', $staff))
            ->assertOk()
            ->assertSee($role->name)
            ->assertSee($linkedUser->username);
    }
}

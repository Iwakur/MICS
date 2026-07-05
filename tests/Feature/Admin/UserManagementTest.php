<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for admin user management.
 *
 * These scenarios protect the first real CRUD surface in the rebuild and the
 * safety rules that stop admins from locking the app out of administration.
 */
class UserManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_create_a_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'username' => 'new-teacher',
            'email' => 'new-teacher@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::Teacher->value,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $createdUser = User::query()->where('username', 'new-teacher')->first();

        $this->assertNotNull($createdUser);
        $this->assertSame(UserRole::Teacher, $createdUser->role);
        $this->assertTrue($createdUser->is_active);
    }

    public function test_admin_can_update_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $teacher), [
            'username' => 'updated-teacher',
            'email' => 'updated-teacher@example.com',
            'password' => '',
            'password_confirmation' => '',
            'role' => UserRole::Admin->value,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $teacher->refresh();

        $this->assertSame('updated-teacher', $teacher->username);
        $this->assertSame('updated-teacher@example.com', $teacher->email);
        $this->assertSame(UserRole::Admin, $teacher->role);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $teacher));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertModelMissing($teacher);
    }

    /**
     * This is the core safety rule behind the current admin CRUD surface.
     */
    public function test_last_active_admin_cannot_be_demoted_or_deactivated(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.users.edit', $admin))
            ->put(route('admin.users.update', $admin), [
                'username' => $admin->username,
                'email' => $admin->email,
                'password' => '',
                'password_confirmation' => '',
                'role' => UserRole::Teacher->value,
                'is_active' => '0',
            ]);

        $response->assertRedirect(route('admin.users.edit', $admin));
        $response->assertSessionHasErrors([
            'role' => 'At least one active administrator must remain in the system.',
        ]);

        $admin->refresh();

        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertTrue($admin->is_active);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->admin()->create();

        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $admin));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHasErrors([
            'danger' => 'You cannot delete your own account.',
        ]);

        $this->assertModelExists($admin);
    }
}

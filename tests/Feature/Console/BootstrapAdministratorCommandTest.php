<?php

namespace Tests\Feature\Console;

use App\Enums\UserRole;
use App\Models\StaffRole;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Console tests for first-administrator bootstrap.
 *
 * This command is a likely deployment/bootstrap entrypoint, so the suite
 * verifies both the happy path and the safe refusal path.
 */
class BootstrapAdministratorCommandTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_command_creates_the_first_linked_administrator_and_staff_profile(): void
    {
        $this->artisan('app:bootstrap-administrator')
            ->expectsQuestion('Administrator username', 'bootstrap-admin')
            ->expectsQuestion('Administrator email', 'bootstrap@example.com')
            ->expectsQuestion('Staff first name', 'Amina')
            ->expectsQuestion('Staff family name (optional)', 'Admin')
            ->expectsQuestion('Password (minimum 12 characters)', 'StrongPassword123')
            ->expectsOutput('Linked administrator and staff profile created. The command will now refuse to run again.')
            ->assertExitCode(0);

        $administrator = User::query()
            ->where('username', 'bootstrap-admin')
            ->firstOrFail();

        $this->assertSame(UserRole::Admin, $administrator->role);
        $this->assertTrue($administrator->is_active);
        $this->assertNotNull($administrator->staffMember);
        $this->assertTrue(Hash::check('StrongPassword123', $administrator->getAuthPassword()));
        $this->assertSame('Amina', $administrator->staffMember->first_name);
        $this->assertSame('Admin', $administrator->staffMember->family_name);
        $this->assertSame('bootstrap@example.com', $administrator->staffMember->email);
        $this->assertSame('Administrator', $administrator->staffMember->role->name);
    }

    public function test_command_refuses_to_run_once_an_active_administrator_exists(): void
    {
        StaffRole::factory()->create(['name' => 'Administrator']);
        User::factory()->admin()->create();

        $this->artisan('app:bootstrap-administrator')
            ->expectsOutput('An active administrator already exists. Use the administrator UI for additional accounts.')
            ->assertExitCode(1);
    }
}

<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the browser login/logout flow.
 *
 * These tests protect the smallest auth surface the project currently has:
 * the login form, session login, inactive-account rejection, and logout.
 */
class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_can_view_the_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Sign in');
    }

    public function test_guest_is_redirected_to_login_when_visiting_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * This proves the current login identifier is username, not email.
     */
    public function test_user_can_log_in_with_username_and_password(): void
    {
        $user = User::factory()->create([
            'username' => 'teacher-one',
        ]);

        $response = $this->post(route('login.store'), [
            'username' => 'teacher-one',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_an_invalid_password(): void
    {
        User::factory()->create([
            'username' => 'teacher-one',
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'username' => 'teacher-one',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /**
     * Business rule on top of normal auth: inactive accounts cannot open sessions.
     */
    public function test_inactive_user_cannot_log_in(): void
    {
        User::factory()->inactive()->create([
            'username' => 'teacher-one',
        ]);

        $response = $this->from(route('login'))->post(route('login.store'), [
            'username' => 'teacher-one',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors([
            'username' => 'This account is inactive.',
        ]);
        $this->assertGuest();
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}

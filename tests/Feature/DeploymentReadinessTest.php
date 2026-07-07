<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\WithCachedConfig;
use Illuminate\Foundation\Testing\WithCachedRoutes;
use Tests\TestCase;

/**
 * Deployment-facing smoke tests.
 *
 * These assertions focus on bootstrap paths that commonly break in container
 * deployments: cached config/routes, entry redirects, and auth throttling.
 */
class DeploymentReadinessTest extends TestCase
{
    use LazilyRefreshDatabase;
    use WithCachedConfig;
    use WithCachedRoutes;

    public function test_guest_entrypoints_boot_with_cached_configuration_and_routes(): void
    {
        $this->get('/')
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in');
    }

    public function test_authenticated_user_is_redirected_away_from_guest_login_with_cached_bootstrap(): void
    {
        $user = User::factory()->teacher()->create();

        $this->actingAs($user)->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_login_route_is_throttled_after_repeated_failed_attempts(): void
    {
        User::factory()->create([
            'username' => 'teacher-one',
        ]);
        $token = 'deployment-test-token';

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->withSession(['_token' => $token])
                ->from(route('login'))
                ->post(route('login.store'), [
                    '_token' => $token,
                    'username' => 'teacher-one',
                    'password' => 'wrong-password',
                ])->assertRedirect(route('login'));
        }

        $this->withSession(['_token' => $token])
            ->from(route('login'))
            ->post(route('login.store'), [
                '_token' => $token,
                'username' => 'teacher-one',
                'password' => 'wrong-password',
            ])->assertTooManyRequests();
    }
}

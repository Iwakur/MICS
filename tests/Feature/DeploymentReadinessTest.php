<?php

/**
 * MICS test coverage: tests Feature DeploymentReadinessTest. See docs/file-reference.md for protected behavior.
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DeploymentReadinessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_readiness_endpoint_confirms_database_connectivity(): void
    {
        $this->get(route('ready'))
            ->assertOk()
            ->assertExactJson(['status' => 'ready']);
    }

    public function test_production_readiness_command_rejects_local_configuration(): void
    {
        $this->artisan('app:check-production-readiness', ['--skip-database' => true])
            ->expectsOutputToContain('APP_ENV must be production.')
            ->expectsOutputToContain('APP_DEBUG must be false.')
            ->assertFailed();
    }

    public function test_production_readiness_command_accepts_safe_core_configuration(): void
    {
        $this->app->instance('env', 'production');
        config([
            'app.env' => 'production',
            'app.debug' => false,
            'app.key' => 'base64:'.base64_encode(str_repeat('a', 32)),
            'app.url' => 'https://mics.example.com',
            'database.default' => 'pgsql',
            'session.secure' => true,
            'session.encrypt' => true,
            'deployment.trusted_proxies' => '10.0.0.0/8',
        ]);

        $this->artisan('app:check-production-readiness', ['--skip-database' => true])
            ->expectsOutputToContain('Production configuration and database readiness checks passed.')
            ->assertSuccessful();
    }
}

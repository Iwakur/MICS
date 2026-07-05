<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('app:check-production-readiness {--skip-database : Skip the live database connection check}')]
#[Description('Validate security-sensitive production configuration before deployment')]
class CheckProductionReadiness extends Command
{
    public function handle(): int
    {
        $errors = $this->configurationErrors();
        $warnings = $this->configurationWarnings();

        if (! $this->option('skip-database')) {
            try {
                DB::connection()->getPdo();
            } catch (Throwable $exception) {
                $errors[] = 'Database connection failed: '.$exception->getMessage();
            }
        }

        foreach ($warnings as $warning) {
            $this->warn($warning);
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->info('Production configuration and database readiness checks passed.');

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function configurationErrors(): array
    {
        $checks = [
            [app()->environment('production'), 'APP_ENV must be production.'],
            [config('app.debug') === false, 'APP_DEBUG must be false.'],
            [filled(config('app.key')), 'APP_KEY must be generated and persistent.'],
            [str_starts_with((string) config('app.url'), 'https://'), 'APP_URL must use HTTPS.'],
            [config('database.default') !== 'sqlite', 'Production must use a managed database, not SQLite.'],
            [config('session.secure') === true, 'SESSION_SECURE_COOKIE must be true.'],
            [config('session.encrypt') === true, 'SESSION_ENCRYPT must be true.'],
            [filled(config('deployment.trusted_proxies')), 'TRUSTED_PROXIES must identify the production proxy or load balancer.'],
        ];

        return collect($checks)->reject(fn (array $check) => $check[0])->pluck(1)->all();
    }

    /** @return list<string> */
    private function configurationWarnings(): array
    {
        $warnings = [];

        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            $warnings[] = 'Mail is non-delivering. This is acceptable until email workflows are enabled.';
        }

        if (in_array(config('queue.default'), ['sync', 'deferred', 'background'], true)) {
            $warnings[] = 'No durable queue is configured. Add a worker before introducing queued jobs.';
        }

        return $warnings;
    }
}

# MICS HUB Command Reference

Run local commands from the repository root. DDEV commands execute in the project containers, keeping PHP and PostgreSQL versions consistent.

## Environment and Dependencies

| Command | Why and expected result |
|---|---|
| `ddev start` | Starts PHP, nginx, and PostgreSQL. Safe and repeatable. |
| `ddev stop` | Stops containers without deleting databases. |
| `ddev poweroff` | Stops all DDEV projects; useful before Docker maintenance. |
| `ddev composer setup` | First-time dependency, environment, migration, npm, and build setup. |
| `ddev composer install` | Restores exact PHP dependencies from `composer.lock`. |
| `ddev npm ci` | Restores exact JavaScript dependencies from `package-lock.json`; preferred in CI. |
| `ddev composer dev` | Runs the local server processes, logs, and Vite watcher until interrupted. |

## Database

| Command | Why and expected result |
|---|---|
| `ddev exec php artisan migrate:status` | Shows which migrations have run without changing data. Start diagnosis here. |
| `ddev exec php artisan migrate` | Applies pending local migrations. Review migration code first. |
| `ddev exec php artisan migrate:rollback` | Reverses the last safe local batch. Do not use blindly after real financial writes. |
| `ddev exec php artisan migrate:fresh --seed` | Deletes every table and rebuilds reviewed reference and local school data. **Destructive; local/test only.** |
| `ddev exec php artisan db:seed` | Adds or refreshes reviewed reference and local school data idempotently. |
| `ddev export-db --file=/tmp/mics-hub.sql.gz` | Creates a DDEV database export before risky local experiments. |
| `ddev import-db --file=/tmp/mics-hub.sql.gz` | Replaces the local database from an export. Stop and verify the target project first. |

## Quality and Testing

| Command | Why and expected result |
|---|---|
| `ddev composer check` | Complete required gate: formatting, analysis, tests, audits, and build. |
| `ddev composer test` | Clears config and runs all PHPUnit tests. |
| `ddev composer test:workflow` | Runs the longitudinal monthly-finance regression subset. |
| `ddev composer analyse` | Runs Larastan level 5; zero new errors are allowed. |
| `ddev exec ./vendor/bin/pint` | Rewrites PHP to project style. Review the diff afterward. |
| `ddev exec ./vendor/bin/pint --test` | Checks formatting without rewriting. |
| `ddev composer audit:security` | Checks locked Composer and npm dependencies for advisories. |
| `ddev npm run build` | Produces versioned frontend assets in ignored `public/build`. |
| `ddev npm run playwright:install` | Downloads the Chromium browser used by Playwright. Needed once per environment. |
| `ddev npm run test:e2e` | Runs browser smoke tests against `PLAYWRIGHT_BASE_URL` or `http://127.0.0.1:8000`. |

## Laravel Diagnostics

| Command | Why and expected result |
|---|---|
| `ddev exec php artisan about` | Displays framework, environment, drivers, and cache state. |
| `ddev exec php artisan route:list --except-vendor` | Lists application URLs, methods, middleware, and controller targets. |
| `ddev exec php artisan config:show database` | Displays resolved non-secret database configuration. Avoid sharing output if it contains credentials. |
| `ddev exec php artisan optimize:clear` | Clears stale config, route, event, and view caches during diagnosis. |
| `ddev exec php artisan optimize` | Builds Laravel caches. |
| `php artisan app:bootstrap-administrator` | One-time interactive setup after first migration; creates linked staff/admin and refuses when an active admin exists. |
| `ddev exec php artisan pail` | Streams Laravel logs for local debugging; stop with Ctrl-C. |

## Git and Releases

```bash
git switch feature
git pull --ff-only origin feature
git status
ddev composer check
git add <reviewed-files>
git commit -m "Describe observable change"
git push origin feature
```

Open a pull request from `feature` to `main`. For a bug use `fix` and include a regression test. Never force-push `main`.

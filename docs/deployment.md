# MICS Deployment Runbook

## Release Preconditions

1. Provision PHP 8.4, PostgreSQL 17, a web server with HTTPS, and Node.js for the build stage.
2. Create the production environment from `.env.production.example`; never commit the real `.env`.
3. Generate `APP_KEY` once with `php artisan key:generate --show` and store it in the deployment secret manager. Do not rotate it casually because encrypted data and sessions depend on it.
4. Restrict database and proxy access to the application network.
5. Point monitoring at `/up` for process liveness and `/ready` for application/database readiness.

## Deployment Sequence

Prefer versioned release directories with shared `.env` and `storage` paths and an atomic `current` symlink. Run these commands from a clean release checkout before switching the symlink:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
npm ci
npm run build
php artisan app:check-production-readiness
php artisan down --retry=30
php artisan migrate --force
php artisan optimize
php artisan up
```

For a compatible additive migration, maintenance mode may be limited to the migration/symlink switch. For a destructive or incompatible schema change, use the expand-and-contract process in `docs/development-conventions.md` instead of relying on a long outage.

Keep the previous release artifact available. Application rollback means switching the `current` symlink back to that artifact; database rollback should normally use a tested backup restore or a forward-fix migration rather than blindly running `migrate:rollback` after financial records exist. Never change tracked PHP or Blade files directly in the active release.

## Processes

MICS currently has no scheduled automatic billing and no queued business workflow. A queue worker and scheduler are therefore not required for the current release. Add supervised `php artisan queue:work` and a once-per-minute `php artisan schedule:run` trigger before enabling queued or scheduled features.

## PostgreSQL Backup

Prefer the hosting provider's encrypted automated backups with point-in-time recovery. Before every schema deployment, also create a release-tagged logical backup:

```bash
pg_dump --format=custom --no-owner --no-acl --file=mics-before-release.dump "$DATABASE_URL"
```

Test restoration in an isolated database, never over production:

```bash
createdb mics_restore_test
pg_restore --clean --if-exists --no-owner --no-acl --dbname=mics_restore_test mics-before-release.dump
```

After restoration, run `php artisan app:check-production-readiness` against the isolated database and verify record counts for users, staff, students, student months, payments, expenses, and billing-month events. Set retention and point-in-time recovery according to the selected host before launch.

## Release Verification

1. Confirm `/up` and `/ready` return HTTP 200.
2. Log in using a newly created administrator account, not seeded demo credentials.
3. Complete the admin and teacher walkthrough in `PLAN.md` on desktop and mobile widths.
4. Confirm teachers receive HTTP 403 for administrator finance and account-management routes.
5. Verify database backup completion and record the restore-test date.

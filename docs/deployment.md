# MICS Deployment Runbook

Target: Ubuntu, nginx, PHP 8.4 FPM, PostgreSQL 17, Git, Composer, and Node.js 22. Use a restricted deployment user that owns the application directories; never run the application as root.

## Release Preconditions

1. Provision PHP 8.4, PostgreSQL 17, a web server with HTTPS, and Node.js for the build stage.
2. Create the production environment from `.env.production.example`; never commit the real `.env`.
3. Generate `APP_KEY` once with `php artisan key:generate --show` and store it in the deployment secret manager. Do not rotate it casually because encrypted data and sessions depend on it.
4. Restrict database and proxy access to the application network.
5. Point monitoring at `/up` for process liveness and `/ready` for application/database readiness.

On the first empty production database, run migrations and then create the first linked administrator interactively:

```bash
php artisan migrate --force
php artisan app:bootstrap-administrator
```

The command hides the password, validates a strong minimum, creates staff and account atomically, and refuses to run once an active administrator exists. Subsequent accounts are created through the UI.

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

The guarded automated equivalent is:

```bash
APP_ROOT=/var/www/mics APP_URL=https://mics.example.com RELEASE_TAG=<tested-commit-or-tag> scripts/deploy.sh
```

It creates a release directory, links shared `.env` and storage, installs/builds, checks production configuration, enables maintenance, migrates, caches, switches `current` atomically, restores service, and verifies health.

The first deployment is manual because `/var/www/mics/current` does not exist yet: create shared `.env`/storage, run `scripts/deploy.sh` from a trusted checkout, then run `app:bootstrap-administrator`. Automatic GitHub deployments begin after that first `current` symlink exists.

For a compatible additive migration, maintenance mode may be limited to the migration/symlink switch. For a destructive or incompatible schema change, use the expand-and-contract process in `docs/development-conventions.md` instead of relying on a long outage.

Keep the previous release artifact available. Application rollback means switching the `current` symlink back to that artifact; database rollback should normally use a tested backup restore or a forward-fix migration rather than blindly running `migrate:rollback` after financial records exist. Never change tracked PHP or Blade files directly in the active release.

## Processes

MICS currently has no scheduled automatic billing and no queued business workflow. A queue worker and scheduler are therefore not required for the current release. Add supervised `php artisan queue:work` and a once-per-minute `php artisan schedule:run` trigger before enabling queued or scheduled features.

## PostgreSQL Backup

Prefer the hosting provider's encrypted automated backups with point-in-time recovery. Before every schema deployment, also create a release-tagged logical backup:

```bash
BACKUP_DIR=/var/backups/mics RELEASE_TAG=v1.1.0 scripts/backup-database.sh
```

Test restoration in an isolated database, never over production:

```bash
RESTORE_CONFIRM=YES scripts/restore-database.sh /var/backups/mics/mics-v1.1.0.dump
```

After restoration, run `php artisan app:check-production-readiness` against the isolated database and verify record counts for users, staff, students, student months, payments, expenses, and billing-month events. Set retention and point-in-time recovery according to the selected host before launch.

## Release Verification

Run `APP_URL=https://mics.example.com scripts/verify-release.sh`, then:

1. Confirm `/up` and `/ready` return HTTP 200.
2. Log in using a newly created administrator account, not seeded demo credentials.
3. Complete the admin and teacher walkthrough in `PLAN.md` on desktop and mobile widths.
4. Confirm teachers receive HTTP 403 for administrator finance and account-management routes.
5. Verify database backup completion and record the restore-test date.

## GitHub Production Environment

Configure protected secrets `DEPLOY_HOST`, `DEPLOY_USER`, `DEPLOY_KEY`, and `APP_ROOT`, plus environment variable `APP_URL`. Protect `main` and the `production` GitHub environment. The deployment job runs only after both quality and browser jobs succeed on `main`.

For code rollback, run `APP_ROOT=/var/www/mics APP_URL=https://mics.example.com TARGET_RELEASE=<directory> scripts/rollback.sh`. It switches code only; migrations are not blindly reversed after business writes.

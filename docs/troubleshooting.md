# MICS Troubleshooting and Incident Guide

Start read-only: record the time, user, URL, release commit, environment, and exact error before changing caches, files, or data.

## Local Application Does Not Start

```bash
ddev describe
ddev logs
ddev restart
ddev composer install
ddev npm ci
ddev exec php artisan optimize:clear
```

`ddev describe` confirms URLs and services. Reinstall only from lock files. Do not add Blade fallback CSS if assets fail—repair Vite/npm.

## HTTP Errors

Use `ddev exec php artisan about`, `ddev exec php artisan pail`, and `ddev exec php artisan route:list --except-vendor`. Keep production `APP_DEBUG=false`. A 419 means an expired session/CSRF token: reload and resubmit; never disable CSRF. A 403 is authorization, 404 is a missing resource, and 429 is throttling.

## Database or Migration Failure

```bash
ddev exec php artisan migrate:status
ddev export-db --file=/tmp/mics-before-repair.sql.gz
ddev exec php artisan migrate
```

Never run `migrate:fresh` or blind rollback on real data. Preserve maintenance mode, inspect the failed SQL, then use a compatible forward fix or tested restore. Never edit a migration already deployed to production.

## Incorrect Student Balance

Check monthly opening balance, charge, adjustment, validated payments/refunds, and next opening balance. Draft payments do not reduce debt. Refunds are negative validated payments. Do not edit totals in SQL; reproduce the defect in a test and use an attributed application correction.

## Month or Reconciliation Problems

A close requires an open month, effective student configuration, and effective catalog rate. A second close is intentionally rejected. Expected bank close uses validated payments by `paid_at`, validated expenses by `month_date`, and the previous reconciled actual close. Explain a real variance instead of forcing the amounts to match.

## CI or Asset Failure

Run `ddev composer check` from a clean checkout and fix the first failing layer. For browser failures inspect Playwright screenshots/traces and the CI application log. If Vite fails, run `ddev npm ci` then `ddev npm run build`; do not embed generated CSS in Blade.

## Failed Deployment or Restore

Before symlink switching, the old release remains active. If maintenance mode remains, confirm safety and run `php artisan up` from the current release. Roll code back with `APP_ROOT`, `APP_URL`, and `TARGET_RELEASE` using `scripts/rollback.sh`; this does not reverse migrations.

Create backups with `scripts/backup-database.sh` and verify them with `pg_restore --list`. Restore into an isolated database first. A production restore requires `RESTORE_CONFIRM=YES`, verified target credentials, maintenance mode, and owner approval.

## Compromised Account or Secret

Deactivate the account, preserve logs, rotate the credential in the VPS/GitHub secret store, terminate sessions when necessary, and redeploy cached configuration. Rotating `APP_KEY` invalidates sessions and may make encrypted values unreadable; do it only with a recovery plan.

# MICS HUB Testing Guide

## Test Layers

- PHPUnit unit tests verify framework-independent money behavior.
- PHPUnit feature tests boot Laravel and verify routes, validation, authorization, persistence, transactions, and finance rules using isolated databases.
- `tests/Feature/Workflows/LongitudinalWorkflowTest.php` simulates July through September, including effective rates, debt carry, partial refund, bank reconciliation, reopen, and validated-record preservation.
- Playwright uses real Chromium against seeded PostgreSQL to verify login, role navigation, finance entry points, and mobile rendering.
- Larastan detects invalid types and relationship assumptions. Pint enforces consistent PHP style.
- Composer/npm audits compare locked dependencies with known security advisories.

## Common Commands

```bash
ddev composer test
ddev exec php artisan test --filter=partial_refunds
ddev composer test:workflow
ddev composer analyse
ddev exec ./vendor/bin/pint --test
ddev npm run build
```

The PHPUnit environment uses in-memory SQLite for speed. CI then migrates, rolls back the final domain migration, remigrates, and seeds PostgreSQL 17 before running Playwright against that database. This catches database-specific schema and browser-bootstrap problems. Concurrency-sensitive balance logic uses transactions and deterministic PostgreSQL lock ordering; SQLite cannot reproduce lock contention.

## Writing Tests

Name tests after observable behavior. Use factories, exact money assertions, allowed and denied authorization cases, and database/model assertions. A defect fix must first add a test that fails for the defect. Finance tests must state whether records are draft, validated, refunded, adjusted, closed, reopened, or reconciled.

Never depend on execution order, current real time, previous test data, network services, or demo passwords outside browser smoke setup. Use `CarbonImmutable::setTestNow()` for month-sensitive rules and restore time afterward.

## Playwright Locally

```bash
ddev npm run playwright:install
ddev exec -u root npx playwright install-deps chromium
ddev exec php artisan migrate:fresh --seed
ddev exec php artisan serve --host=0.0.0.0 --port=8000
PLAYWRIGHT_BASE_URL=http://127.0.0.1:8000 ddev npm run test:e2e
```

`migrate:fresh` deletes the selected database; confirm it is local before running it. In normal DDEV use, prefer testing against the existing `https://mics-hub.ddev.site` by setting `PLAYWRIGHT_BASE_URL` accordingly. Browser system libraries installed inside DDEV disappear when its web container is rebuilt; rerun `install-deps` when Chromium reports a missing shared library. CI installs both browser and libraries automatically.

## Failure Interpretation

- PHPUnit failure: inspect the first failure, not the cascade; rerun its file or filter.
- Larastan failure: fix source types/relationships before considering a baseline entry.
- Pint failure: run Pint, then review all formatting changes.
- Playwright failure: inspect screenshot/trace and application log; determine whether the selector or product behavior changed.
- Audit failure: confirm affected runtime dependency, upgrade the lock file deliberately, rerun all checks, and document the advisory.

# MICS HUB

MICS HUB is a Laravel 13 school-operations application for staff access, assigned students, lesson catalogs, monthly charges, payments/refunds, salaries, expenses, and bank reconciliation. It targets PHP 8.4, PostgreSQL 17, Tailwind CSS 4, and Vite.

## What the Product Does

- Administrators manage users, staff, roles, students, lesson types, plans, payments, expenses, and monthly finance.
- Teachers manage only assigned students and their monthly lesson counts.
- Billing and catalog changes are effective-dated, so later edits do not rewrite earlier months.
- Month closing snapshots charges and salary drafts. Validated financial records are immutable.
- Student debt is derived from monthly charges, adjustments, validated payments, and linked refunds.
- Bank reconciliation compares expected cash movement with the actual closing balance and records variances.

## Five-Minute Local Setup

Prerequisites: Docker, DDEV, Git, and an available local port range for DDEV.

```bash
git clone git@github.com:Iwakur/MICS.git
cd MICS
ddev start
ddev composer setup
ddev exec php artisan db:seed
```

`ddev start` creates the PHP/nginx/PostgreSQL containers. `ddev composer setup` installs PHP and JavaScript dependencies, creates local environment configuration, migrates the database, and builds frontend assets. Seeding is safe to repeat locally.

Open `https://mics-hub.ddev.site`. Demo accounts are `admin` / `password` and `teacher` / `password`; they are created only in local environments.

## Daily Development

```bash
ddev composer dev
ddev composer check
```

The first command runs the application, logs, and Vite watcher. The second runs Pint, Larastan, PHPUnit, dependency audits, and the asset build.

Use `ddev composer test:workflow` for the three-month finance regression and `ddev npm run test:e2e` for browser smoke tests after installing Chromium with `ddev npm run playwright:install`.

## Source of Truth

1. Migrations, models, services, policies, and tests define implemented behavior.
2. This README records durable product direction.
3. [PLAN.md](PLAN.md) records active work and unresolved decisions.
4. [docs/schema.dbml](docs/schema.dbml) is a generated diagram snapshot; migrations remain authoritative.

Students are business records, not login accounts. Every active login links to active staff. An administrator may hold any staff role; a teacher login requires teaching-capable staff.

## Documentation Map

- [Architecture guide](docs/codebase-guide.md): Laravel request flow, domain services, database history, and source boundaries.
- [Product workflows](docs/product-workflows.md): operational steps for setup and a complete monthly cycle.
- [Command reference](docs/commands.md): what every supported command does and when to use it.
- [Testing guide](docs/testing.md): PHPUnit, PostgreSQL, Playwright, static analysis, audits, and CI.
- [Development guide](docs/development-conventions.md): safe feature and bug-fix workflow.
- [Troubleshooting](docs/troubleshooting.md): common local, CI, database, asset, and finance failures.
- [Maintained file reference](docs/file-reference.md): ownership of project paths.

Never commit `.env`, rewrite validated finance records, or run `migrate:fresh` against a database containing real data.

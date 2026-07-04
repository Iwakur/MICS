# Repository Guidelines

## Project Structure & Module Organization

This is a Laravel 13 application.

- Application code lives in `app/`.
- HTTP controllers belong in `app/Http/Controllers`.
- Models belong in `app/Models`.
- Service providers belong in `app/Providers`.
- Browser routes belong in `routes/web.php`.
- Console commands belong in `routes/console.php`.
- Database migrations, factories, and seeders belong in `database/`.
- Front-end JavaScript, CSS, and Blade templates live in `resources/js`, `resources/css`, and `resources/views`.
- Vite publishes compiled assets through `public/`.
- Configuration is in `config/`.
- Writable runtime files belong in `storage/`.

Tests are split into `tests/Unit` for isolated logic and `tests/Feature` for framework, database, and HTTP behavior.

## Build, Test, and Development Commands

- `ddev start` starts the PHP 8.4, nginx, and PostgreSQL 17 development environment.
- `ddev composer setup` installs dependencies, prepares `.env`, runs migrations, installs npm packages, and builds assets.
- `ddev composer dev` runs the Laravel server, queue listener, log viewer, and Vite watcher together.
- `ddev npm run build` creates production front-end assets.
- `ddev composer test` clears cached configuration and runs the PHPUnit suite.
- `ddev exec ./vendor/bin/pint` formats PHP code.

If working without DDEV, run the underlying `composer`, `php artisan`, and `npm` commands directly with compatible PHP and database services.

## Coding Style & Naming Conventions

Follow PSR-12 and Laravel conventions.

- Use four spaces for PHP and JavaScript.
- Use two spaces for YAML.
- Use LF endings and a final newline as defined by `.editorconfig`.
- Run Pint before submitting.
- Use `StudlyCase` for classes.
- Use `camelCase` for methods and variables.
- Use `snake_case` for database columns.
- Name migrations descriptively, such as `create_orders_table` or `add_status_to_orders_table`.
- Keep controllers thin and move reusable domain behavior into focused classes.

## Testing Guidelines

PHPUnit 12 is configured in `phpunit.xml` with an in-memory SQLite database.

- Test files must end in `Test.php`.
- Name test methods after observable behavior.
- Add feature tests for routes, validation, authorization, and persistence.
- Add unit tests for framework-independent logic.
- Run `ddev composer test` before opening a pull request.
- New behavior and bug fixes should include regression tests.

## Collaboration & Learning

The project owner wants to understand and write the application, not receive unexplained finished code.

- Explain the Laravel concept before implementation.
- Recommend an approach with reasons and mention realistic alternatives when useful.
- Keep changes small, controlled, and reversible.
- After each implementation step, explain what changed, why it changed, what Laravel concept it uses, and what the next step is.
- Record the latest active work in `PLAN.md`.

Implementation is allowed when the owner asks for it directly or when the current step has already been explained and approved.

Use the documentation with these roles:

- `README.md` is the durable project truth for agreed product direction and major decisions.
- `PLAN.md` tracks the latest active work, current blockers, and the next controlled steps.
- Do not use temporary handoff documents such as `hash.md`; fold anything still relevant into `PLAN.md` or `README.md`.

## MICS Domain & Legacy Reference

MICS is a clean Laravel rebuild of a school operations system.

- The current Laravel code, migrations, and tests are always the implementation source of truth.
- Product intent and a proposed simplified schema are documented in `legacy(self-created)/README.md` and `legacy(self-created)/database/schema.dbml`.
- The older plain-PHP application under `legacy(self-created)/MICS_legacy/` is historical reference only.

Use the legacy material to understand workflows and terminology, not as code to copy.

- Reimplement accepted behavior with Laravel conventions.
- Confirm ambiguous rules before committing to a schema.
- Students are tracked records, not authenticated users.
- Every administrator is a teacher, but standard teachers may access only their own profile and assigned students.
- Keep administrator access separate from staff business roles.
- Defer non-teaching staff workflows for now, while allowing their records and future balances to fit the model.
- Derive student debt from `student_months` and validated payments; never store a mutable current-debt total on `students`.
- Avoid reviving the legacy accounting journal, SQL console, custom bootstrap provisioning, or unnecessary repository layers unless explicitly requested.

## Laravel Boost Notes

Follow the Laravel Boost guidance already installed for this repository.

- Use Laravel-native structure and APIs.
- Prefer focused tests over ad-hoc verification scripts.
- Use `php artisan make:` commands for new Laravel classes when possible.
- Run Pint on changed PHP files before finalizing PHP edits.
- Use PHPUnit classes, not Pest.

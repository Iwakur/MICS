# Repository Guidelines

## Project Structure & Module Organization

This is a Laravel 13 application. Application code lives in `app/`; HTTP controllers belong in `app/Http/Controllers`, models in `app/Models`, and service providers in `app/Providers`. Define browser routes in `routes/web.php` and console commands in `routes/console.php`. Database migrations, factories, and seeders are under `database/`. Front-end JavaScript, CSS, and Blade templates live in `resources/js`, `resources/css`, and `resources/views`; Vite publishes compiled assets through `public/`. Configuration is in `config/`, while writable runtime files belong in `storage/`.

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

Follow PSR-12 and Laravel conventions. Use four spaces for PHP and JavaScript, two for YAML, LF endings, and a final newline as defined by `.editorconfig`. Run Pint before submitting. Use `StudlyCase` for classes, `camelCase` for methods and variables, and `snake_case` for database columns. Name migrations descriptively, for example `create_orders_table` or `add_status_to_orders_table`. Keep controllers thin and move reusable domain behavior into focused classes.

## Testing Guidelines

PHPUnit 12 is configured in `phpunit.xml` with an in-memory SQLite database. Test files must end in `Test.php`; name test methods after observable behavior. Add feature tests for routes, validation, authorization, and persistence, and unit tests for framework-independent logic. Run `ddev composer test` before opening a pull request. No coverage threshold is currently enforced, but new behavior and bug fixes should include regression tests.

## Commit & Pull Request Guidelines

Recent history uses short, imperative summaries such as `Initialize Laravel project with DDEV and PostgreSQL`. Keep commits focused and explain the outcome, not the editing process. Pull requests should include a concise purpose, implementation notes, test results, and linked issues. Include screenshots for visible UI changes and call out migrations, environment changes, or deployment steps explicitly. Never commit `.env`, secrets, generated dependencies, or runtime files.

## Collaboration & Learning

The project owner wants to understand and write the application, not receive unexplained finished code. Default to guidance before implementation: explain the Laravel concept, recommend an approach with reasons, identify tradeoffs, and provide small ordered instructions in `PLAN.md`. Challenge uncertain or risky ideas constructively and suggest a more maintainable Laravel-native alternative. Do not implement application code unless the owner explicitly asks after reviewing the proposed steps. When a guided step is completed, inspect it, explain any issues, and update the plan with the next step.

## MICS Domain & Legacy Reference

MICS is a clean Laravel rebuild of a school operations system. The current Laravel code, migrations, and tests are always the implementation source of truth. Product intent and a proposed simplified schema are documented in `legacy(self-created)/README.md` and `legacy(self-created)/database/schema.dbml`. The older plain-PHP application under `legacy(self-created)/MICS_legacy/` is historical reference only.

Use the legacy material to understand workflows and terminology, not as code to copy. Reimplement accepted behavior with Laravel conventions and confirm ambiguous rules before committing to a schema. The target scope includes staff and users, students, lesson types and plans, monthly student balances, validated payments and expenses, and simple bank-month closing. Students are tracked records, not authenticated users. Every administrator is a teacher, but standard teachers may access only their own profile and assigned students. Keep administrator access separate from staff business roles. Defer non-teaching staff workflows for now, while allowing their records and future balances to fit the model. Derive student debt from `student_months` and validated payments; never store a mutable current-debt total on `students`. Avoid reviving the legacy accounting journal, SQL console, custom bootstrap provisioning, or unnecessary repository layers unless explicitly requested.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>

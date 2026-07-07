# MICS HUB Laravel Architecture Guide

## Purpose and Scope

This guide explains how Laravel works in this repository and how MICS HUB uses it. It covers maintained application code and the framework concepts needed to understand it. Generated dependencies in `vendor/`, compiled files in `public/build/`, runtime files in `storage/`, and historical code in `legacy(self-created)/` are not maintained application source and are not documented line by line.

For a path-by-path inventory, see `docs/file-reference.md`. Product rules live in `README.md`; current work and quality gates live in `PLAN.md`.

## Laravel in One Request

Laravel is the application framework. It supplies the HTTP kernel, service container, router, middleware pipeline, authentication, validation, database ORM, views, console commands, and testing integration. MICS HUB adds school-specific rules on top.

A browser request follows this sequence:

1. The web server sends the request to `public/index.php`.
2. `bootstrap/app.php` creates the Laravel `Application`, registers route files, replaces trusted-proxy handling, aliases MICS HUB middleware, and defines exception rendering.
3. Laravel loads providers from `bootstrap/providers.php`; `AppServiceProvider` is the MICS HUB extension point for application-wide boot logic.
4. The router matches a declaration in `routes/web.php`.
5. Global and route middleware run. Authentication resolves the current `User`; `EnsureUserIsActive` revokes inactive sessions; `EnsureUserIsAdmin` protects the admin area.
6. Route model binding converts identifiers such as `{payment}` into Eloquent models such as `Payment`.
7. A Form Request authorizes and validates mutation input when the controller method type-hints one.
8. Laravel’s service container constructs the controller and injects services such as `MonthClosingService`.
9. The controller coordinates the use case and returns a redirect, JSON response, or Blade view.
10. Eloquent executes parameterized SQL through the configured database connection.
11. Blade escapes `{{ }}` output and renders HTML; Vite-built Tailwind assets provide styling.
12. Session and cookie middleware persist flash messages, validation errors, and authentication state in the response.

## Service Container and Dependency Injection

Laravel’s container creates classes from controller method signatures. For example, `FinanceSummaryController` requests `FinanceSummaryService`; no manual `new` call is required because the service has resolvable dependencies. This keeps controllers focused on HTTP concerns and makes domain behavior independently testable.

MICS HUB currently uses convention-based automatic injection. No custom binding is needed in `AppServiceProvider` because services are concrete classes without interfaces.

## Routing and Middleware

`routes/web.php` defines the browser surface:

- guest-only login routes use `guest`;
- all application routes use `auth` and `active`;
- the `/dashboard` dispatcher selects the role-specific home;
- `/admin/*` additionally uses `admin`;
- `/teacher/*` relies on controller-level ownership checks for assigned students;

Resource routes generate conventional CRUD names and URLs. Explicit routes handle non-CRUD transitions such as payment validation/refund, month closing/reopening, and bank reconciliation/reopening.

Middleware answers cross-cutting access questions before controllers run:

- Laravel `auth` requires a resolvable authenticated user;
- `EnsureUserIsActive` logs out accounts deactivated after login;
- `EnsureUserIsAdmin` requires the system access role `admin`;
- `TrustProxies` reads configured proxy addresses so HTTPS and client metadata are interpreted safely behind a load balancer.

System access roles and staff business roles are separate. `User.role` controls authorization; `StaffRole` describes organizational responsibility and whether that staff member may teach.

## Authentication and Sessions

Laravel’s session guard authenticates `User` records. `LoginRequest` validates credentials, blocks inactive accounts, calls `Auth::attempt`, and rate-limits repeated failures through the login route middleware. Successful login regenerates the session identifier to prevent session fixation. Logout invalidates the session and regenerates the CSRF token.

Sessions use the configured Laravel session driver and cookie settings.

## Controllers

Controllers translate HTTP requests into application actions:

- CRUD controllers load form options, call validated model operations, and redirect with flash messages;
- role-specific student controllers enforce teacher ownership;
- invokable dashboard and finance controllers each handle one GET endpoint;
- financial transitions delegate calculations to services and use database transactions where several rows must change atomically.

Controllers should not contain reusable accounting calculations. Those belong in `app/Services`.

## Form Requests

Form Requests combine authorization and validation:

- `authorize()` decides whether the current account and current model state permit the action;
- `rules()` describes accepted fields, types, ranges, enums, and foreign keys;
- `after()` performs relational checks that cannot be expressed as a simple field rule;
- helper methods such as `studentData()` return normalized data for the controller.

Controllers consume `$request->validated()` or safe helper methods, never unrestricted request input. Conditional rules enforce the difference between per-lesson and plan-based students, fixed and dynamic compensation, and draft versus validated financial records.

## Eloquent Models and Relationships

Eloquent maps database tables to PHP classes. MICS HUB models use:

- `#[Fillable]` to define mass-assignable fields;
- `casts()` for enums, booleans, dates, datetimes, and fixed-decimal strings;
- `belongsTo`, `hasOne`, and `hasMany` relationships;
- local scopes such as `validated()` for reusable query constraints;
- domain methods such as `StudentMonth::closingBalance()`.

Relationships describe both navigation and query intent. Examples:

- a `User` optionally belongs to one `Staff` record;
- a `Student` belongs to one assigned teacher/staff member;
- a `StudentMonth` owns payments;
- a generated salary `Expense` owns preserved `SalaryDraftSource` rows;
- a payment refund belongs to its immutable original payment; an original may have multiple refunds.

Views never perform database queries. Controllers and services eager-load relationships to avoid N+1 query behavior.

## Enums

Backed enums constrain persisted string states while remaining portable between PostgreSQL and SQLite tests. MICS HUB uses enums for user role, student status, billing type, staff compensation mode, financial review status, and billing-month status. Database columns stay strings so migrations remain portable; model casts expose enum instances in PHP.

## Database Transactions and Locks

Financial state transitions use `DB::transaction()` so all writes succeed or all roll back. Row locks protect transitions that could otherwise race:

- month closing locks the billing-month row;
- lesson-count updates lock the month lifecycle row;
- payment validation locks the draft payment;
- payment refund locks the original payment and rejects cumulative refunds above the received amount.

Unique constraints provide a second line of defense for one student row per month, one effective configuration/rate per owner/month, one generated salary per staff/month key, and one lifecycle month record.

## Monthly Accounting Model

MICS HUB is operational accounting, not a general ledger.

Student balance formula:

```text
closing balance = opening balance + generated charge + manual adjustment - validated payments
```

Only validated payments affect debt. A refund is a linked negative validated payment, so it restores debt without changing history. Student credits are reported separately from positive debt so one overpayment cannot hide another student’s receivable.

Month closing is manual and selected by month. It:

1. calculates per-lesson and plan charges;
2. creates or refreshes draft student charges;
3. creates fixed or dynamic salary drafts;
4. snapshots salary source rows;
5. carries closing balances forward;
6. marks the month closed and records a lifecycle event.

Reopening requires a reason. Reclosing may refresh drafts but skips validated student charges and salary expenses.

## Services

`MonthClosingService` owns charge/salary preview and transactional generation. `StudentBalanceService` creates missing monthly balance rows from prior history and propagates corrections through existing future rows. `FinanceSummaryService` builds read-only monthly totals and the student debt ledger.

These classes are domain services: they contain behavior shared by multiple HTTP actions and make accounting invariants visible outside controllers.

## Migrations and Schema History

Migrations are ordered schema changes. Laravel records applied filenames in the `migrations` table. Applied migrations are historical records and should not be edited; future changes require new forward migrations.

MICS HUB consolidated its domain history into two commented baseline migrations: school structure and monthly finance structure. Together with Laravel's three framework migrations, a fresh installation runs five migrations. After they are applied these baseline files become immutable history; all later changes must use new migrations.

Foreign-key deletion behavior is intentional:

- student-owned months and payments cascade when a student is intentionally deleted;
- staff references in financial history become null where history must survive;
- catalog and assignment references restrict deletion and use application-level archiving;
- payment originals cannot be deleted while a refund points to them.

Indexes cover foreign keys, statuses, dates, unique business keys, and common compound filters.

## Factories and Seeders

Factories create valid isolated records for tests and development. Factory states such as `admin()`, `teacher()`, and `validated()` make intent explicit.

`DatabaseSeeder` always creates the reviewed Ukrainian reference catalogs. In local and testing environments it also creates exactly one administrator, one teacher, and twelve pupils through `SchoolDataSeeder`. Local users are `admin` / `password` and `teacher` / `password`; no financial history or private source values are seeded.

Seeders are idempotent: repeated runs update or reuse known records rather than duplicating them.

## Blade, Tailwind, and Vite

Blade templates define server-rendered pages. `layouts/app.blade.php` owns the authenticated shell, grouped navigation, flash messages, and validation summaries. Feature views provide page-specific tables and forms. Shared form partials reduce repeated field markup.

`resources/css/app.css` imports Tailwind CSS 4 and defines MICS HUB theme tokens plus reusable component classes such as surfaces, inputs, buttons, badges, and navigation links. `resources/js/app.js` is the Vite JavaScript entrypoint and is intentionally minimal.

Vite compiles assets into ignored `public/build/` output. Blade uses `@vite`; there is intentionally no fallback CSS when assets are missing.

## Testing

PHPUnit feature tests boot Laravel and exercise routes, middleware, validation, Eloquent persistence, transactions, and rendered views. Unit tests are reserved for framework-independent logic. The suite uses SQLite in memory for speed.

`LazilyRefreshDatabase` resets schema state only when a test touches the database. Tests cover authentication, role boundaries, CRUD, teacher scoping, effective dating, three-month calculations, refunds, lifecycle audits, reconciliation, seeds, and schema constraints.

## Console

Artisan is Laravel’s CLI. Important commands are:

```bash
php artisan migrate --force
php artisan optimize
php artisan route:list
php artisan schedule:list
```

The current release has no scheduled task and no queued business job. Do not run infrastructure merely for appearance; add supervised queue/scheduler processes when features actually use them.

## Source-of-Truth Boundaries

Use this authority order:

1. current migrations, models, services, routes, and tests;
2. `README.md` for durable product decisions;
3. `PLAN.md` for release blockers and next work;
4. this architecture guide and `docs/file-reference.md` for learning;
5. `legacy(self-created)/` only for historical terminology and workflow research.

Never copy framework internals from `vendor/` into application code. Extend Laravel through controllers, middleware, requests, services, providers, configuration, and documented public APIs.

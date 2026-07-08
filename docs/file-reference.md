# MICS HUB Maintained File Reference

## How to Use This Reference

Each entry describes one maintained source file or one intentionally grouped generated area. `vendor/`, `node_modules/`, `public/build/`, `bootstrap/cache/`, `storage/`, and DDEV-generated files are outputs or third-party implementation details; they should be understood by role but not edited as MICS HUB source. Historical files under `legacy(self-created)/` are excluded because they are not part of the running Laravel application.

## Folder Map

- `.agents/`: project-local coding-agent skills and repository workflow guidance; not runtime application code.
- `.ddev/`: reproducible local PHP, nginx, PostgreSQL, mail, and tooling environment.
- `docker/`: production container runtime files for Caddy, PHP, Supervisor, and entrypoint behavior.
- `app/`: all MICS HUB runtime PHP classes.
- `app/Console/Commands/`: custom Artisan commands.
- `app/Enums/`: finite backed-string domain states persisted by Eloquent.
- `app/Http/Controllers/`: browser request coordinators, split into shared, authentication, admin, and teacher areas.
- `app/Http/Middleware/`: cross-cutting account and infrastructure request checks.
- `app/Http/Requests/`: authorization, validation, and input normalization before controllers run.
- `app/Models/`: Eloquent table mappings, relationships, casts, scopes, and small domain calculations.
- `app/Providers/`: Laravel container and application boot extension points.
- `app/Policies/`: record-level authorization rules discovered by Laravel.
- `app/Services/`: reusable accounting and monthly workflow behavior outside controllers.
- `app/Support/`: small framework-independent domain utilities.
- `bootstrap/`: Laravel application construction, provider list, and generated optimization cache.
- `config/`: environment-backed runtime configuration; `env()` calls belong here rather than application classes.
- `database/factories/`: valid randomized model builders for tests.
- `database/migrations/`: five ordered first-release schema files: three Laravel infrastructure and two MICS HUB domain baselines.
- `database/seeders/`: idempotent reviewed reference and local school data.
- `docs/`: architecture and exhaustive file responsibility documentation.
- `legacy(self-created)/`: historical product research only; never loaded by the Laravel application.
- `public/`: web-server document root and generated frontend output.
- `resources/css/`: Tailwind CSS source and MICS HUB design tokens/components.
- `resources/js/`: browser JavaScript source entrypoint.
- `resources/views/`: Blade templates grouped by admin, teacher, authentication, layouts, and shared partials.
- `routes/`: browser and console route registration.
- `storage/`: writable logs, sessions, caches, compiled views, and temporary runtime artifacts.
- `tests/Feature/`: full Laravel behavior, authorization, persistence, rendering, and workflow tests.
- `tests/Unit/`: framework-independent tests.
- `vendor/`: Composer-installed Laravel/framework dependencies; read-only and replaceable.
- `node_modules/`: npm-installed Tailwind/Vite dependencies; read-only and replaceable.

## Root Project Files

- `.env.example`: safe local environment template; includes application, database, session, queue, cache, and mail variables.
- `.env.docker.example`: production-oriented container environment template for Compose or VPS deployment.
- `.editorconfig`: line endings, indentation, encoding, and final-newline rules shared by editors.
- `.dockerignore`: keeps non-runtime files and local state out of Docker build contexts.
- `.gitattributes`: Git text normalization and export behavior.
- `.gitignore`: excludes secrets, dependencies, runtime state, and compiled assets.
- `AGENTS.md`: repository collaboration, Laravel learning, testing, and documentation rules.
- `README.md`: durable product direction, implemented model, business rules, and local entrypoints.
- `PLAN.md`: active work, confirmed constraints, quality checklist, demo walkthrough, and final blockers.
- `composer.json`: PHP dependency manifest, PSR-4 autoloading, and setup/dev/test scripts.
- `composer.lock`: exact PHP dependency versions used for reproducible installation.
- `compose.prod.yml`: exact-image orchestration for the application, PostgreSQL, and the one-off migration task.
- `Dockerfile`: multi-stage immutable build for one PHP-FPM, Caddy, and application image.
- `.github/workflows/release.yml`: tests pull requests and pushes to `main`; semantic-version Git tags rerun the gate and publish the matching exact GHCR image after success.
- `package.json`: frontend dependency and Vite command manifest.
- `package-lock.json`: exact npm dependency graph.
- `phpunit.xml`: PHPUnit environment; uses in-memory SQLite and testing-specific services.
- `pint.json`: Laravel formatter configuration excluding the read-only historical legacy application.
- `phpstan.neon`: Larastan paths, analysis level, and framework extension configuration.
- `phpstan-baseline.neon`: reviewed existing Laravel inference findings; new findings still fail CI.
- `vite.config.js`: Tailwind/Vite build inputs and development-server integration.
- `boost.json`: Laravel Boost tooling configuration.
- `artisan`: Laravel console entrypoint that boots the application and dispatches commands.

## Documentation

- `docs/codebase-guide.md`: framework and MICS HUB architecture guide, including request lifecycle and accounting design.
- `docs/docker.md`: immutable Docker deployment design, build steps, migration flow, and VPS constraints.
- `docs/file-reference.md`: this path-by-path maintained-source inventory.
- `docs/development-conventions.md`: safe change workflow, code placement, money, locking, authorization, migrations, and testing rules.
- `docs/commands.md`: supported local, database, quality, diagnostic, Git, and release commands with safety notes.
- `docs/testing.md`: PHPUnit, PostgreSQL, Playwright, static-analysis, audit, and CI learning guide.
- `docs/product-workflows.md`: initial setup, monthly operations, corrections, reconciliation, and three-month example.
- `docs/troubleshooting.md`: local, CI, finance, database, and incident recovery guide.

## Bootstrap and Public Entry

- `public/index.php`: HTTP entrypoint; loads Composer, boots Laravel, and handles the request.
- `public/.htaccess`: Apache rewrite rules for forwarding application URLs to `index.php`.
- `public/favicon.ico`: browser icon asset.
- `public/robots.txt`: crawler policy.
- `bootstrap/app.php`: Laravel 13 application builder; registers routes, aliases, and exception behavior.
- `bootstrap/providers.php`: provider list loaded during application boot.
- `bootstrap/cache/.gitignore`: keeps generated framework cache files out of Git.

## Docker Runtime Files

- `docker/entrypoint.sh`: app-container startup guard that checks `APP_KEY`, prepares writable directories, and optionally builds Laravel caches.
- `docker/php/php.ini`: production-leaning PHP and opcache settings for the PHP-FPM container.
- `docker/caddy/Caddyfile`: Caddy static-file serving and local PHP-FPM proxy configuration.
- `docker/supervisor/supervisord.conf`: keeps PHP-FPM and Caddy running in the single application container.

## Console

- `app/Console/Commands/BootstrapAdministrator.php`: one-time interactive, validated, atomic creation of the first linked administrator.
- `routes/console.php`: closure-command and scheduler registration; currently contains only Laravel’s example quote command and no scheduled business task.

## Enums

- `app/Enums/UserRole.php`: authenticated access roles: administrator and teacher.
- `app/Enums/StudentStatus.php`: lifecycle states used to activate, pause, or archive students.
- `app/Enums/StudentBillingType.php`: per-lesson versus recurring plan billing.
- `app/Enums/StaffCompensationMode.php`: fixed salary versus dynamic student-derived compensation.
- `app/Enums/ReviewStatus.php`: draft versus validated financial review state.
- `app/Enums/BillingMonthStatus.php`: open versus closed month lifecycle state.

## Models

- `app/Models/User.php`: authenticatable login account, password hashing, role cast/helpers, activity state, and optional one-to-one staff link.
- `app/Models/Staff.php`: staff business identity, role, compensation settings, assigned students, generated expenses, and optional login account.
- `app/Models/StaffRole.php`: editable organizational role with active and teaching-capability flags.
- `app/Models/Student.php`: non-authenticated student identity, teacher assignment, status, billing mode, offering selection, discount, and monthly history.
- `app/Models/LessonType.php`: per-lesson catalog item containing duration, school price, teacher share, archive flag, and assigned students.
- `app/Models/LessonTypeRate.php`: effective-dated school and teacher per-lesson prices.
- `app/Models/Plan.php`: recurring plan containing lesson metadata, school monthly price, teacher monthly amount, archive flag, and assigned students.
- `app/Models/PlanRate.php`: effective-dated plan prices and teacher monthly amount.
- `app/Models/StudentConfiguration.php`: effective-dated student assignment, status, billing, catalog, start, and discount snapshot.
- `app/Models/StudentMonth.php`: one student/month snapshot with lesson count, opening balance, charge, adjustment, validation state, payments, and closing-balance calculation.
- `app/Models/Payment.php`: draft/validated payment, original/refund relationships, refundable totals, and validated query scope.
- `app/Models/ExpenseCategory.php`: stable category for irregular expenses and salary drafts.
- `app/Models/Expense.php`: manual expense or generated salary, review state, generation key, staff/category links, and salary source details.
- `app/Models/SalaryDraftSource.php`: immutable calculation detail for each fixed, per-lesson, or plan salary contribution.
- `app/Models/BillingMonth.php`: month lock state, closer attribution, close timestamp, and lifecycle event history.
- `app/Models/BillingMonthEvent.php`: append-only close/reopen audit event with actor, reason, and timestamp.
- `app/Models/BankMonth.php`: expected/actual bank close, reconciliation attribution, status, and audit history.
- `app/Models/BankMonthEvent.php`: append-only reconcile/reopen audit event.

## Policies and Support

- `app/Policies/StudentPolicy.php`: record-level student update access for administrators and the assigned active teacher.
- `app/Support/Money.php`: exact conversion between decimal database amounts, integer cents used for arithmetic, and display values.
- `app/Support/EffectiveMonth.php`: selects the earliest month in which mutable configuration may safely take effect.

## Domain Services

- `app/Services/MonthClosingService.php`: previews and transactionally generates charges, salary drafts, source snapshots, next balances, and lifecycle changes while protecting validated records.
- `app/Services/StudentBalanceService.php`: transactionally creates missing months from prior closing balance and propagates corrections through locked future months.
- `app/Services/FinanceSummaryService.php`: exact-cent monthly aggregation for charges, validated payments, positive debt, student credit, salaries, manual expenses, and student ledger rows.
- `app/Services/BankReconciliationService.php`: calculates expected bank balance and transactionally reconciles/reopens audited bank months.

## Base and Shared Controllers

- `app/Http/Controllers/Controller.php`: framework base controller inherited by MICS HUB controllers.
- `app/Http/Controllers/DashboardController.php`: authenticated role dispatcher for `/dashboard`.
- `app/Http/Controllers/MonthlyLessonCountController.php`: shared admin/teacher lesson-count screen, scoped student query, month locking, and transactional count updates.

## Authentication Controller

- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`: renders login, regenerates sessions after authentication, records last login, and securely logs out.

## Admin Controllers

- `app/Http/Controllers/Admin/AdminDashboardController.php`: dashboard counts for people, compensation modes, and billing catalogs.
- `app/Http/Controllers/Admin/UserController.php`: account CRUD with password handling, self-delete prevention, and last-active-admin protection.
- `app/Http/Controllers/Admin/StaffController.php`: staff CRUD/archive plus transactional one-to-one linking and unlinking of login accounts.
- `app/Http/Controllers/Admin/StaffRoleController.php`: organizational role CRUD with archive behavior that preserves assigned staff.
- `app/Http/Controllers/Admin/StudentController.php`: unrestricted admin student CRUD/archive with current archived catalog options retained on edit.
- `app/Http/Controllers/Admin/LessonTypeController.php`: per-lesson catalog CRUD and archive-by-flag behavior.
- `app/Http/Controllers/Admin/PlanController.php`: recurring-plan catalog CRUD and archive-by-flag behavior.
- `app/Http/Controllers/Admin/MonthClosingController.php`: selected-month preview plus close/reopen HTTP actions delegated to the closing service.
- `app/Http/Controllers/Admin/StudentChargeController.php`: monthly charge review, attributed adjustment, validation, and balance propagation.
- `app/Http/Controllers/Admin/PaymentController.php`: payment draft CRUD, atomic validation, immutable partial/full refunds, and balance propagation.
- `app/Http/Controllers/Admin/ExpenseController.php`: manual expense CRUD and generated salary review, with validated/generated deletion protections.
- `app/Http/Controllers/Admin/FinanceSummaryController.php`: selected-month parser and read-only finance summary endpoint.
- `app/Http/Controllers/Admin/ExpenseCategoryController.php`: category CRUD with archive-on-use deletion behavior.
- `app/Http/Controllers/Admin/BankMonthController.php`: bank reconciliation preview, reconcile, and reopen HTTP actions.

## Teacher Controllers

- `app/Http/Controllers/Teacher/TeacherDashboardController.php`: teacher home and linked-profile summary.
- `app/Http/Controllers/Teacher/StudentController.php`: teacher-scoped student list/create/edit; forces assignment to the current active teaching staff profile and blocks cross-teacher access.

## Middleware

- `app/Http/Middleware/EnsureUserIsAdmin.php`: denies admin routes unless the authenticated system role is admin.
- `app/Http/Middleware/EnsureUserIsActive.php`: revokes an existing authenticated session immediately when its account becomes inactive.
- `app/Http/Middleware/TrustProxies.php`: loads explicit proxy addresses after configuration is available so forwarded HTTPS/client headers are trusted only from configured infrastructure.

## Authentication Request

- `app/Http/Requests/Auth/LoginRequest.php`: credential validation, rate-limit key management, inactive-account rejection, and `Auth::attempt` orchestration.

## Shared Requests

- `app/Http/Requests/SaveStudentRequest.php`: conditional student validation and normalized data for both admin and teacher flows; verifies teaching-capable assignment.
- `app/Http/Requests/SaveMonthlyLessonCountsRequest.php`: validates month/count arrays and exposes normalized integer counts.

## Admin Requests

- `app/Http/Requests/Admin/StoreUserRequest.php`: validates unique username/email, role, activity, and required password for new accounts.
- `app/Http/Requests/Admin/UpdateUserRequest.php`: validates edits while ignoring the current user for uniqueness and allowing blank password retention.
- `app/Http/Requests/Admin/StoreStaffRequest.php`: validates staff identity, compensation mode, role, salary conditions, and optional available account link.
- `app/Http/Requests/Admin/UpdateStaffRequest.php`: validates staff edits and allows retention of the current role/account links.
- `app/Http/Requests/Admin/SaveStaffRoleRequest.php`: validates role name, note, active flag, and teaching capability.
- `app/Http/Requests/Admin/SaveLessonTypeRequest.php`: validates catalog name, duration, school price, teacher share, note, and assignability.
- `app/Http/Requests/Admin/SavePlanRequest.php`: validates plan metadata, school/teacher amounts, note, and assignability.
- `app/Http/Requests/Admin/CloseBillingMonthRequest.php`: admin authorization and strict `Y-m` selected-month conversion.
- `app/Http/Requests/Admin/ReopenBillingMonthRequest.php`: admin authorization plus mandatory meaningful audit reason.
- `app/Http/Requests/Admin/UpdateStudentChargeRequest.php`: permits draft-only charge adjustment/validation and requires an adjustment reason.
- `app/Http/Requests/Admin/SavePaymentRequest.php`: permits draft-only payment create/edit and validates student, month, receipt date, positive amount, method, and note.
- `app/Http/Requests/Admin/ReversePaymentRequest.php`: validates attributed partial/full refunds and prevents cumulative over-refund.
- `app/Http/Requests/Admin/SaveExpenseRequest.php`: permits draft-only expense/salary edits and validates category, optional staff, month, amount, status, and note.
- `app/Http/Requests/Admin/SaveExpenseCategoryRequest.php`: validates unique category metadata and active state.
- `app/Http/Requests/Admin/ReconcileBankMonthRequest.php`: validates selected month, actual close, variance reason, and note.
- `app/Http/Requests/Admin/ReopenBankMonthRequest.php`: requires an administrator and meaningful bank-reopen reason.

## Provider

- `app/Providers/AppServiceProvider.php`: application boot extension point; prevents accidental lazy loading locally.

## Route Files

- `routes/web.php`: all guest, authenticated, admin, teacher, finance-transition, and logout browser routes.
- `routes/console.php`: console closure commands and future schedule definitions.

## Configuration

- `config/app.php`: application name, environment, debug mode, URL, locale, encryption key/cipher, and maintenance settings.
- `config/auth.php`: session guard, Eloquent user provider, and password reset behavior.
- `config/cache.php`: database/file/Redis/etc. cache stores and cache-key prefix.
- `config/database.php`: SQLite, MySQL, MariaDB, PostgreSQL, SQL Server, and Redis connections.
- `config/filesystems.php`: local/public/cloud filesystem disks and public storage link.
- `config/logging.php`: stack, file, stderr, syslog, Slack, and emergency log channels.
- `config/mail.php`: mail transports and global sender; current MICS HUB release has no email workflow.
- `config/queue.php`: queue connections, batching, and failed-job storage; current MICS HUB release defines no queued job.
- `config/services.php`: credentials/configuration placeholders for external service integrations.
- `config/session.php`: database session storage, lifetime, encryption, cookie security, domain, and SameSite behavior.

## Migrations

- `database/migrations/0001_01_01_000000_create_users_table.php`: users, password reset tokens, and database sessions.
- `database/migrations/0001_01_01_000001_create_cache_table.php`: database cache and cache locks.
- `database/migrations/0001_01_01_000002_create_jobs_table.php`: queued jobs, batches, and failed jobs.
- `database/migrations/2026_07_05_010000_create_school_structure.php`: consolidated first-release domain schema for staff roles, staff, account links, lesson types, plans, and students.
- `database/migrations/2026_07_05_020000_create_monthly_finance_structure.php`: consolidated first-release schema for monthly balances, payments/reversals, expenses/salaries, month lifecycle audit, and bank snapshots.

## Generated Schema Documentation

- `docs/schema.dbml`: generated application-schema snapshot for DBML-compatible diagram tools; Laravel migrations remain authoritative.

## Factories

- `database/factories/UserFactory.php`: users plus admin, teacher, and inactive states.
- `database/factories/StaffRoleFactory.php`: organizational roles and capability flags.
- `database/factories/StaffFactory.php`: valid staff with role and compensation settings.
- `database/factories/StudentFactory.php`: per-lesson students plus plan-based state.
- `database/factories/LessonTypeFactory.php`: per-lesson catalog values.
- `database/factories/PlanFactory.php`: plan catalog values.
- `database/factories/StudentMonthFactory.php`: monthly balance snapshots.
- `database/factories/PaymentFactory.php`: draft payments plus validated state.
- `database/factories/ExpenseCategoryFactory.php`: expense categories.
- `database/factories/ExpenseFactory.php`: manual/generated expenses plus validated state.
- `database/factories/SalaryDraftSourceFactory.php`: salary calculation source rows.
- `database/factories/BillingMonthFactory.php`: open/closed-capable month lifecycle records.
- `database/factories/BillingMonthEventFactory.php`: attributed close lifecycle event.
- `database/factories/BankMonthFactory.php`: bank opening/closing snapshot.

## Seeders

- `database/seeders/DatabaseSeeder.php`: runs reference data always and reviewed school data in local/testing environments.
- `database/seeders/ReferenceDataSeeder.php`: idempotent Ukrainian teaching role, lesson rates, plans, and expense categories reviewed from the local customer source.
- `database/seeders/SchoolDataSeeder.php`: local/testing-only administrator, Максим teacher account, and twelve source-derived pupils without financial history or private payment data.

## Frontend Assets

- `resources/css/app.css`: Tailwind CSS 4 import, font sources, theme variables, page atmosphere, reusable surfaces, forms, buttons, navigation, badges, and flash components.
- `resources/js/app.js`: Vite JavaScript entrypoint; intentionally empty until client-side behavior is justified.

## Shared and Authentication Views

- `resources/views/layouts/app.blade.php`: authenticated layout, responsive sidebar, grouped navigation, user state, logout, flash messages, and validation summary.
- `resources/views/auth/login.blade.php`: guest credential form with CSRF protection and validation display.
- `resources/views/dashboard.blade.php`: inactive starter dashboard retained as a harmless reference; active flow redirects to role dashboards.
- `resources/views/lesson-counts/index.blade.php`: shared admin/teacher selected-month lesson-count table and closed-month lock state.
- `resources/views/students/partials/form.blade.php`: shared conditional student fields for admin and teacher create/edit pages.

## Admin Views

- `resources/views/admin/dashboard.blade.php`: grouped operational counts and links for people, access, catalogs, and monthly finance.
- `resources/views/admin/finance-summary.blade.php`: selected-month cards and student debt/credit ledger.
- `resources/views/admin/month-closing/index.blade.php`: close preview, student/salary details, close/reopen controls, and lifecycle audit.
- `resources/views/admin/users/index.blade.php`: account table and access management actions.
- `resources/views/admin/users/create.blade.php`: new account form.
- `resources/views/admin/users/edit.blade.php`: account edit/password-retention form.
- `resources/views/admin/staff/index.blade.php`: staff, role, linked account, compensation, assignment count, and archive actions.
- `resources/views/admin/staff/create.blade.php`: staff creation wrapper.
- `resources/views/admin/staff/edit.blade.php`: staff edit wrapper.
- `resources/views/admin/staff/partials/form.blade.php`: shared staff identity, role, compensation, account-link, and activity fields.
- `resources/views/admin/staff-roles/index.blade.php`: role capability/activity table.
- `resources/views/admin/staff-roles/create.blade.php`: role creation wrapper.
- `resources/views/admin/staff-roles/edit.blade.php`: role edit wrapper.
- `resources/views/admin/staff-roles/partials/form.blade.php`: shared role fields.
- `resources/views/admin/students/index.blade.php`: all-student table, assignment, billing, status, and archive actions.
- `resources/views/admin/students/create.blade.php`: admin student creation wrapper.
- `resources/views/admin/students/edit.blade.php`: admin student edit wrapper.
- `resources/views/admin/lesson-types/index.blade.php`: lesson catalog rates, usage count, activity, and actions.
- `resources/views/admin/lesson-types/create.blade.php`: lesson type creation wrapper.
- `resources/views/admin/lesson-types/edit.blade.php`: lesson type edit wrapper.
- `resources/views/admin/lesson-types/partials/form.blade.php`: shared duration, school rate, teacher share, note, and activity fields.
- `resources/views/admin/plans/index.blade.php`: recurring plan rates, usage count, activity, and actions.
- `resources/views/admin/plans/create.blade.php`: plan creation wrapper.
- `resources/views/admin/plans/edit.blade.php`: plan edit wrapper.
- `resources/views/admin/plans/partials/form.blade.php`: shared plan metadata and rate fields.
- `resources/views/admin/student-charges/index.blade.php`: selected-month generated charge review table.
- `resources/views/admin/student-charges/edit.blade.php`: adjustment reason, note, and validation transition.
- `resources/views/admin/payments/index.blade.php`: selected-month payment/refund table and status/type indicators.
- `resources/views/admin/payments/create.blade.php`: payment draft creation wrapper.
- `resources/views/admin/payments/edit.blade.php`: draft review, validation, immutable detail, and partial/full refund controls.
- `resources/views/admin/payments/partials/form.blade.php`: shared student, month, date, amount, method, and evidence fields.
- `resources/views/admin/expenses/index.blade.php`: salary/manual expense list, status, source count, filters, and actions.
- `resources/views/admin/expenses/create.blade.php`: manual expense creation wrapper.
- `resources/views/admin/expenses/edit.blade.php`: expense/salary review and source details.
- `resources/views/admin/expenses/partials/form.blade.php`: shared category, staff, month, amount, status, and note fields.

## Teacher Views

- `resources/views/teacher/dashboard.blade.php`: teacher profile and assigned-work overview.
- `resources/views/teacher/students/index.blade.php`: only the authenticated teacher’s assigned students.
- `resources/views/teacher/students/create.blade.php`: teacher-scoped student creation wrapper.
- `resources/views/teacher/students/edit.blade.php`: teacher-scoped student editing wrapper.

## Test Infrastructure and Tests

- `tests/TestCase.php`: shared Laravel application test base.
- `tests/Unit/MoneyTest.php`: exact decimal-to-cent conversion, database formatting, negative values, and invalid-precision coverage.
- `tests/Feature/Workflows/ApplicationWorkflowSmokeTest.php`: renders every maintained admin and teacher workflow page against connected seeded data to catch route/controller/Blade contract failures.
- `tests/Feature/Auth/AuthenticationTest.php`: login page, guest redirect, success, bad password, inactivity, and logout.
- `tests/Feature/Auth/DashboardAccessTest.php`: role dispatch, admin denial, and immediate deactivated-session revocation.
- `tests/Feature/Admin/UserManagementTest.php`: account CRUD and last-admin/self-delete safety.
- `tests/Feature/Admin/StaffManagementTest.php`: staff/account linking, replacement, conflict, archive, compensation validation, authorization, and inactive-option retention.
- `tests/Feature/Admin/StaffRoleManagementTest.php`: seeded teacher role, CRUD, archive preservation, and teacher denial.
- `tests/Feature/Students/StudentManagementTest.php`: admin/teacher student CRUD, billing validation, assignment capability, archive, ownership, and archived-option retention.
- `tests/Feature/Admin/CatalogManagementTest.php`: lesson/plan CRUD, archive, non-negative rates, and authorization.
- `tests/Feature/LessonCounts/MonthlyLessonCountTest.php`: admin/teacher scope, eligibility, persistence, and closed-month locking.
- `tests/Feature/Admin/MonthClosingTest.php`: generated charges/salaries/balances, idempotency, authorization, lifecycle audit, reopen/reclose, and validated-record protection.
- `tests/Feature/Admin/StudentChargeReviewTest.php`: attributed adjustments, validation, reason requirement, immutability, and carry-forward.
- `tests/Feature/Admin/PaymentManagementTest.php`: draft CRUD, validation, immutability, authorization, multiple refunds, over-refund protection, propagation, and prior-balance carry.
- `tests/Feature/Admin/ExpenseManagementTest.php`: manual expense and generated salary review, validation, correction, and delete protection.
- `tests/Feature/Admin/FinanceSummaryTest.php`: separation of drafts/validated values, positive debt, student credit, expense classes, rendering, and authorization.
- `tests/Feature/Infrastructure/DatabaseSeederTest.php`: connected idempotent reference/demo seeds.
- `tests/Feature/Infrastructure/DeploymentReadinessTest.php`: cached bootstrap, guest entrypoints, and login throttling used by container deployments.
- `tests/Feature/Localization/LocalePreferenceTest.php`: persisted locale selection and English/Ukrainian rendering and formatting.
- `tests/Feature/Admin/AdminDashboardStatisticsTest.php`: effective-month dashboard student statistics.
- `tests/Feature/Domain/DatabaseSchemaTest.php`: relationships, constraints, balance semantics, cascades, preservation, and unique keys.
- `tests/Feature/Admin/BankReconciliationTest.php`: expected/actual totals, attribution, variance requirements, audit reopen, and authorization.
- `tests/Feature/Admin/ExpenseCategoryManagementTest.php`: category CRUD, archive/delete behavior, and authorization.
- `tests/Feature/Workflows/LongitudinalWorkflowTest.php`: deterministic three-month effective-rate, debt, refund, salary, reconciliation, and reopen regression.
- `tests/Browser/core-workflows.spec.js`: real Chromium admin, teacher, finance navigation, and mobile smoke checks.

## Generated and Third-Party Areas

- `vendor/`: Composer-installed Laravel and PHP package source. Read framework internals only for debugging; never modify them because reinstall/update replaces changes.
- `node_modules/`: npm-installed Tailwind/Vite dependencies; never edit directly.
- `public/build/`: Vite output generated by `npm run build`.
- `storage/`: logs, sessions, caches, compiled views, and other writable runtime data.
- `bootstrap/cache/`: optimized package, service, route, event, and configuration caches generated by Artisan.
- `.ddev/`: local container/web/database orchestration. Maintain deliberate project overrides; treat generated compose files as DDEV output.

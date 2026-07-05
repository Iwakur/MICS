# MICS Codebase Guide

## Purpose

This guide explains the current source structure of the Laravel rebuild in practical terms. It exists to help the project owner study the repository without having to infer each folder's role from framework conventions alone.

## Root and Project Setup

- `README.md`: durable product truth, current product direction, UI direction, and development entrypoints.
- `PLAN.md`: latest active work only. This is the short living plan, not the full project history.
- `AGENTS.md`: collaboration and implementation rules for coding agents working in this repo.
- `composer.json`: PHP package manifest plus project scripts such as `setup`, `dev`, and `test`.
- `package.json`: frontend package manifest. Right now the frontend entrypoints are Vite build and dev.
- `vite.config.js`: Vite dev/build configuration, including the secure DDEV hot-reload origin.
- `phpunit.xml`: PHPUnit test configuration.
- `boost.json`: Laravel Boost configuration used by the local agent tooling.

## Application Code (`app/`)

### Models and enums

- `app/Models/User.php`: the authenticated user model. It holds login account fields, role helpers, casts, and default attribute values.
- `app/Enums/UserRole.php`: role enum for authenticated users. Current values are `admin` and `teacher`.
- `app/UserRole.php`: currently duplicates the enum namespace/path idea and should be treated as a stray legacy file until explicitly cleaned up in a future maintenance pass.

### Controllers

- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`: shows the login form, starts the session after successful auth, and logs users out.
- `app/Http/Controllers/DashboardController.php`: shared dashboard entrypoint. It redirects users to the correct role-specific dashboard.
- `app/Http/Controllers/Admin/AdminDashboardController.php`: feeds the admin dashboard summary counts.
- `app/Http/Controllers/Teacher/TeacherDashboardController.php`: returns the teacher dashboard.
- `app/Http/Controllers/Admin/UserController.php`: first real admin CRUD controller. Manages users and protects last-admin safety rules.
- `app/Http/Controllers/Controller.php`: base Laravel controller.

### Form requests and middleware

- `app/Http/Requests/Auth/LoginRequest.php`: validates login input and performs the login attempt, including the inactive-account rule.
- `app/Http/Requests/Admin/StoreUserRequest.php`: validates new users created by admins.
- `app/Http/Requests/Admin/UpdateUserRequest.php`: validates user edits and allows blank password to mean "keep current password".
- `app/Http/Middleware/EnsureUserIsAdmin.php`: blocks non-admin users from reaching admin-only routes.

### Providers

- `app/Providers/AppServiceProvider.php`: standard Laravel provider placeholder. It currently has no custom boot logic.

## Routes and Bootstrap

- `routes/web.php`: browser routes for login, logout, dashboard dispatch, admin dashboard, teacher dashboard, and admin user CRUD.
- `routes/console.php`: console route definitions.
- `bootstrap/app.php`: Laravel 13 bootstrap file. Registers routing, middleware aliases, and API exception rendering behavior.

## Views and Frontend

### Blade views (`resources/views/`)

- `resources/views/layouts/app.blade.php`: shared authenticated shell with header, sidebar, flash messages, and content region.
- `resources/views/auth/login.blade.php`: guest login page.
- `resources/views/admin/dashboard.blade.php`: admin home screen.
- `resources/views/teacher/dashboard.blade.php`: teacher home screen.
- `resources/views/admin/users/index.blade.php`: admin user list and management table.
- `resources/views/admin/users/create.blade.php`: admin create-user form.
- `resources/views/admin/users/edit.blade.php`: admin edit-user form.
- `resources/views/dashboard.blade.php`: older temporary dashboard file. The active route flow now prefers role-specific dashboards.
- `resources/views/welcome.blade.php`: default Laravel welcome screen, not part of the active app flow right now.

### Frontend assets

- `resources/css/app.css`: Tailwind v4 entry file plus the shared blue-dark tokens and component classes used across the visible app.
- `resources/js/app.js`: JavaScript entrypoint. It is intentionally minimal right now.

## Database Layer (`database/`)

- `database/migrations/0001_01_01_000000_create_users_table.php`: current users, password reset, and sessions schema.
- `database/migrations/0001_01_01_000001_create_cache_table.php`: Laravel cache schema.
- `database/migrations/0001_01_01_000002_create_jobs_table.php`: Laravel queue/jobs schema.
- `database/factories/UserFactory.php`: generates users for tests and development, including admin/teacher/inactive states.
- `database/seeders/DatabaseSeeder.php`: seeds the default local `admin` account.

## Tests (`tests/`)

- `tests/TestCase.php`: shared Laravel test base.
- `tests/Feature/Auth/AuthenticationTest.php`: login/logout feature tests.
- `tests/Feature/DashboardAccessTest.php`: role-aware dashboard redirect and admin access tests.
- `tests/Feature/Admin/UserManagementTest.php`: admin CRUD behavior and safety-rule tests.
- `tests/Feature/ExampleTest.php`, `tests/Unit/ExampleTest.php`: default Laravel starter tests, useful as references until replaced.

## Configuration (`config/`)

Important files in the current app:

- `config/auth.php`: authentication guards/providers.
- `config/session.php`: session storage behavior. The current app uses the database session driver.
- `config/database.php`: PostgreSQL and test database configuration.
- `config/app.php`, `config/cache.php`, `config/queue.php`, `config/mail.php`, `config/logging.php`, `config/filesystems.php`, `config/services.php`: standard Laravel environment-backed configuration.

These files are mostly framework defaults right now, but they still matter because they describe runtime behavior even before business modules grow.

## Infrastructure and Local Environment

- `.ddev/`: local environment definition for PHP, PostgreSQL, webserver, and tooling.
- `.agents/skills/`: project-local agent guidance used during implementation.

The DDEV files are infrastructure, not application logic, but they are worth maintaining because local runtime consistency matters in this repo.

## Current Request Flow Study Guide

1. A guest requests `/login`.
2. `AuthenticatedSessionController@create` returns the login Blade view.
3. The login form posts to `login.store`.
4. `LoginRequest` validates input, blocks inactive users, and calls `Auth::attempt(...)`.
5. `AuthenticatedSessionController@store` regenerates the session and redirects to `/dashboard`.
6. `DashboardController` checks the authenticated user role.
7. Admins are redirected to `/admin/dashboard`; teachers are redirected to `/teacher/dashboard`.
8. Admin routes also pass through `EnsureUserIsAdmin`.
9. Admin user CRUD uses Form Requests for field validation and `UserController` for high-level safety rules.

## UI Study Guide

The visible app currently follows one design system:

- a shared authenticated shell
- blue-dark background and surface palette
- token-based component classes from `resources/css/app.css`
- Blade templates choose page structure; `app.css` now provides the consistent visual language

If Vite or npm assets are down, the repo intentionally does not add rescue CSS inside Blade. That is a real asset-pipeline issue to solve, not a UI fallback case.

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

# MICS Project

## Current Status

MICS is a school and academy operations system being rebuilt cleanly on Laravel 13, PHP 8.4, PostgreSQL 17, Tailwind CSS 4, and Vite. The repository now contains:

- username/password authentication
- role-aware dashboard routing
- separate admin and teacher dashboard surfaces
- the first admin CRUD screen for user management

The previous project and its database designs are retained locally under `legacy(self-created)/` as product research. They describe intended behavior, but they are not code to port directly and are not the source of truth for the running Laravel application.

## Product Direction

MICS should support straightforward school operations without becoming a full accounting platform. Its planned areas are:

- staff, teacher, user, and access management
- student records and teacher assignment
- lesson types and student plans
- monthly student charges and balance tracking
- payment and expense review
- simple monthly bank closing
- separate admin and teacher workflows

Students are tracked business records and do not receive login accounts. Every administrator is also a teacher: administrators receive the full operational dashboard, while standard teachers use a separate dashboard where they can maintain their own profile and manage only their assigned students. Non-teaching staff and balances owed to or by them are part of the longer-term product scope, but their workflows are deferred from the first release.

The rebuild should favor Laravel conventions, direct CRUD workflows, explicit business services, and testable behavior. Avoid reproducing the legacy application's custom framework, automatic bootstrap provisioning, SQL console, deep repository layering, or debit/credit journal unless a later requirement explicitly calls for them.

## Project Structure Walkthrough

High-level source layout:

- `app/`: controllers, form requests, middleware, models, enums, and providers.
- `routes/`: browser and console route definitions.
- `resources/views/`: Blade templates for guest, admin, teacher, and shared layout UI.
- `resources/css/`: Tailwind v4 entry file plus shared blue-dark design tokens and component classes.
- `resources/js/`: JavaScript entry file, currently minimal.
- `database/`: migrations, factories, and seeders.
- `tests/`: feature and unit tests.
- `bootstrap/`: Laravel 13 application bootstrap and middleware alias registration.
- `config/`: runtime configuration for auth, session, database, queue, cache, and related services.
- `.ddev/`: local development environment definition.
- `docs/codebase-guide.md`: deeper codebase walkthrough for studying the repo file-by-file.

If you are learning the codebase, start with:

1. `routes/web.php`
2. `app/Http/Requests/Auth/LoginRequest.php`
3. `app/Http/Controllers/DashboardController.php`
4. `resources/views/layouts/app.blade.php`
5. `app/Http/Controllers/Admin/UserController.php`
6. `tests/Feature/Auth/AuthenticationTest.php`
7. `tests/Feature/Admin/UserManagementTest.php`

## Developer Study Guide

### Current browser request flow

1. A guest visits `/login`.
2. `AuthenticatedSessionController@create` returns the login page.
3. The login form posts to `login.store`.
4. `LoginRequest` validates the input, checks the inactive-user rule, and performs the auth attempt.
5. `AuthenticatedSessionController@store` regenerates the session and redirects to `/dashboard`.
6. `DashboardController` sends the user to the admin or teacher dashboard depending on role.
7. Admin pages are additionally protected by the `admin` middleware alias from `bootstrap/app.php`.
8. Admin user management uses Form Requests for field validation and `UserController` for higher-level safety rules.

### Current admin user-management rules

The first CRUD surface in the rebuild manages users safely:

- `username` must stay unique.
- `email` must stay unique.
- a blank password on edit means "keep the current password".
- an admin cannot delete their own account.
- the last active administrator cannot be demoted, deactivated, or deleted.

## Implemented Data Model

The simplified legacy DBML has been translated into Laravel migrations, Eloquent models, factories, and feature tests. Its principal entities are:

- `staff` and `staff_roles` for business identities and responsibilities
- `users` for login accounts and system access roles
- `students` for identity, assignment, status, and billing configuration
- `lesson_types` and `plans` for billable offerings
- `student_months` for one balance record per student per month
- `payments` linked to a specific student month
- `expenses` and `expense_categories` for reviewed outgoings
- `bank_months` for simple monthly opening and closing snapshots

Access role and business role are separate concepts: a user's role controls authorization, while a staff role describes their function in the organization. Student debt must not be stored as a mutable total on `students`; it is derived from monthly history.

Staff compensation is explicit: fixed staff use a configured salary amount, while dynamic staff later receive a monthly calculation from their assigned students. Lesson types store both the student's per-lesson price and the teacher's per-lesson earning. Plans store the student's recurring monthly price and the teacher's recurring monthly earning. Administrators maintain both catalogs through archive-safe CRUD screens; rate changes are intended for future month closing and must not rewrite historical snapshots.

Student profiles use one billing mode at a time. Administrators may assign, edit, pause, reactivate, or archive any student. Teachers may create and edit only students assigned to their linked active staff profile and cannot reassign or archive them. Per-lesson counts are not stored on the student profile because they belong to a specific billing month.

Staff roles are editable business metadata, separate from login access roles. Every staff role has explicit active and teaching-capability flags. The default `Teacher` role is seeded idempotently, and only active staff with a teaching-capable role may receive student assignments.

The non-production seeder provides connected reference and demo data for every implemented domain area. Run `ddev exec php artisan db:seed` repeatedly without creating duplicates. Local demo logins are reset to `admin` / `password` and `teacher` / `password`. Production seeding creates reference catalogs only, never demo people or financial records.

The current user-management screen predates staff management, so `users.staff_id` is temporarily nullable. When present it is unique, preserving a one-to-one staff/login relationship. It should become required only after account creation can select or create the corresponding staff record.

The intended monthly calculation is:

```text
closing_balance = opening_balance + charge_amount + manual_adjustment - validated_payments
```

Only validated payments and expenses count as real financial activity. Draft records remain reviewable without affecting totals. A month's closing student balance becomes the next month's opening balance.

## Implementation Source of Truth

During development, use this order of authority:

1. Current Laravel migrations, models, tests, and application code describe what exists.
2. This README describes the agreed product direction.
3. `docs/codebase-guide.md` explains the current codebase layout in more detail.
4. `legacy(self-created)/database/schema.dbml` supplies the initial database proposal.
5. `legacy(self-created)/MICS_legacy/` is historical evidence only.

Before implementing another legacy idea, translate it into Laravel terminology, confirm its business rule, and cover it with migrations and feature tests. Do not assume that historical tables, statuses, or workflows are final requirements.

## Frontend UI Direction

The UI direction for MICS should stay intentionally restrained:

- modern minimal layout
- dark-blue primary visual direction
- low-noise surfaces and simple hierarchy
- consistency across admin and teacher areas
- no decorative fallback styling when Vite or npm assets are unavailable

If the frontend asset pipeline is down, the interface may render raw or partially unstyled. Treat that as a real asset-pipeline problem to fix, not a case for extra Blade-level rescue CSS.

## Local Development

```bash
ddev start
ddev composer setup
ddev composer dev
```

Run the test suite with `ddev composer test`, format PHP with `ddev exec ./vendor/bin/pint`, and build production assets with `ddev npm run build`.

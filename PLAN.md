# MICS Development Plan

## Purpose

This is the living roadmap for rebuilding MICS while learning Laravel deliberately. Update it after each completed feature, important decision, or change in scope. The goal is a useful school operations system that is understandable, tested, deployable, and maintainable—not a copy of the legacy application.

## Current State

- Clean Laravel 13 application running through DDEV
- PHP 8.4, PostgreSQL 17, Tailwind CSS 4, and Vite
- PHPUnit, Pint, Laravel Boost, and PAO installed
- Default Laravel users migration and model only
- Legacy product notes and DBML available under `legacy(self-created)/`
- No MICS business feature is implemented yet

## Confirmed Product Decisions

- Use Laravel conventions before introducing custom architecture.
- Keep admin and teacher access separate from staff business roles.
- Students are tracked records, not authenticated users.
- Every administrator is a teacher, but not every teacher is an administrator.
- Administrators have full dashboard access; standard teachers have a separate dashboard limited to their profile and assigned students.
- Track non-teaching staff and their financial balances later; defer their operational workflows from the first release.
- Track student debt through monthly records, not a mutable field on `students`.
- Only validated payments and expenses affect financial totals.
- Keep finance operational and traceable, but avoid full double-entry accounting.
- Treat legacy files as design evidence, never as implementation truth.
- Build one complete vertical feature at a time: schema, model, authorization, UI, and tests.

## Delivery Roadmap

### Phase 1 — Foundation

- Define the smallest usable release and clarify ambiguous business rules.
- Configure application identity, locale, timezone, and PostgreSQL environments.
- Add username/password authentication and a minimal application layout.
- Establish admin and teacher authorization with policies or gates.
- Document deployment targets, backups, secrets, and production requirements.

**Complete when:** users can sign in, authorization is tested, and local setup is repeatable.

### Phase 2 — Staff and Access

- Create staff roles and staff records.
- Link each login account to one staff member.
- Model teaching as a business role and administration as an access permission.
- Build admin CRUD for staff and user access.
- Support activation/deactivation without destroying history.
- Give standard teachers access only to their profile and assigned students.
- Defer non-teaching staff workflows while keeping the data model extensible for them.

**Learning focus:** migrations, Eloquent relationships, validation, policies, factories, and feature tests.

### Phase 3 — Students and Offerings

- Build lesson types and plans.
- Build student CRUD, status handling, teacher assignment, discounts, and billing configuration.
- Give teachers access only to their assigned students.

**Learning focus:** route model binding, form requests, scoped queries, Blade components, and pagination.

### Phase 4 — Monthly Balances and Payments

- Create one `student_month` per student and month.
- Implement explicit monthly charge creation and carry-forward rules.
- Add draft/validated payments linked to a student month.
- Calculate balances from recorded history and test all edge cases.

**Complete when:** balances are reproducible, explainable, and protected against duplicate monthly rows.

### Phase 5 — Expenses and Bank Closing

- Add expense categories and draft/validated expenses.
- Add monthly bank opening/closing snapshots.
- Build monthly summaries for income, expenses, and outstanding student balances.

### Phase 6 — Production Readiness

- Add auditability for important financial and access changes.
- Review validation, authorization, CSRF protection, rate limits, and sensitive data handling.
- Add database backups, restore instructions, logging, monitoring, and error reporting.
- Run the full test suite and perform deployment rehearsal with production-like configuration.
- Write operator documentation for common maintenance tasks.

## Immediate Next Step

Build the first vertical slice: **authentication, role-based dashboard routing, and a minimal login page**. Authentication answers “who is signed in”; authorization answers “what may they access.” Implement and test them separately.

### Recommended User Design

Adapt Laravel's existing `users` migration and model instead of copying the legacy authentication code.

| Field | Purpose |
| --- | --- |
| `id` | Primary key |
| `username` | Unique login identifier and temporary display name until staff profiles exist |
| `email` | Unique contact/recovery address |
| `password` | Laravel-hashed password; do not call it `password_hash` |
| `role` | Dashboard access: `admin` or `teacher` |
| `is_active` | Disables access without deleting history |
| `last_login_at` | Nullable audit timestamp |
| `remember_token` | Laravel's remember-me support |
| timestamps | Creation and update history |

Do not add `staff_id` yet. Add it with a foreign key when the `staff` table is introduced; this avoids a nullable circular first step. At that point, every real teacher account should link to exactly one staff record. Represent `role` with a PHP backed enum and a database string so authorization remains readable and portable.

### Step 1 Review

The first migration draft correctly added a unique username and retained Laravel's password, session, and password-reset structure. Before Step 1 is complete:

- replace `isAdmin` with a `role` string defaulting to `teacher`;
- rename `isActive` to `is_active` to follow Laravel database naming;
- add nullable `last_login_at`;
- remove `email_verified_at` because email verification is not part of the first release; and
- keep email required and unique for future account recovery.

After these corrections, rebuild the disposable local database and inspect the resulting `users` table. Step 2 begins only after the schema matches this plan.

### Acceptance Criteria

- A guest sees a minimal username/password login form.
- Correct active credentials create an authenticated session.
- Invalid credentials show a generic error without revealing which field was wrong.
- Inactive users cannot sign in.
- Login attempts are rate-limited and the session ID is regenerated after success.
- Admin users reach the full dashboard placeholder.
- Teacher users reach the teacher dashboard placeholder.
- Authenticated users cannot reopen the login page and can log out safely.
- Tests cover successful login, failure, inactive users, role redirects, guest protection, and logout.

### Guided Implementation Steps

Complete one step, inspect and understand it, then continue. Do not generate the whole feature in one pass.

1. **Schema:** update the not-yet-deployed default users migration with the agreed fields. Learn why unique constraints, nullability, defaults, and indexes belong in the database.
2. **Domain types:** create the access-role enum and update `User` fillable, hidden, and cast definitions. Keep Laravel's `hashed` password cast.
3. **Test data:** update `UserFactory` with valid defaults plus named admin, teacher, and inactive states.
4. **Routes and controller:** define guest login routes and an authenticated logout route. Use a dedicated login request/controller rather than placing logic in route closures.
5. **Authentication behavior:** validate credentials, apply throttling, regenerate the session after login, and invalidate/regenerate the CSRF token on logout.
6. **Authorization:** redirect by access role and protect each dashboard route. Do not confuse the `admin` permission with the teacher business role.
7. **Blade UI:** create one minimal layout and login view with labels, validation errors, CSRF protection, and accessible focus states.
8. **Tests:** write focused PHPUnit feature tests before styling beyond the minimum.
9. **Verification:** run the focused tests, Pint, then manually test both roles in the browser.

### CSS Decision

Use the existing `resources/css/app.css` as the single CSS entrypoint. Prefer Tailwind utility classes in Blade and small reusable Blade components. Do not create page-specific stylesheets yet; split CSS only when repeated custom styles become difficult to manage. This keeps Vite configuration and the initial UI simple without preventing later organization.

## Working Method

For every feature, first explain the relevant Laravel concepts and the proposed design. Then implement in small reviewable steps. The owner should be able to explain the data model, request flow, authorization, validation, tests, and operational impact before the feature is considered complete.

Avoid hidden automation and premature abstractions. Prefer explicit code, database constraints, policies, form requests, transactions for multi-record financial changes, and tests that describe business behavior.

## Decision Log

Record durable decisions here with the date, decision, reason, and consequences.

| Date | Decision | Reason / Consequence |
| --- | --- | --- |
| 2026-07-01 | Rebuild MICS cleanly on Laravel 13 | Legacy behavior informs requirements, but new code follows Laravel conventions. |
| 2026-07-01 | Use monthly student balance records | Preserves history and prevents an unsynchronized current-debt field. |
| 2026-07-01 | Exclude full accounting from initial scope | Keeps the application focused and maintainable. |
| 2026-07-01 | Separate teaching role from administrator access | Every administrator teaches, while standard teachers receive restricted access. |
| 2026-07-01 | Treat students as non-authenticated records | Student accounts are operational records to track, not application logins. |
| 2026-07-01 | Defer non-teaching staff workflows | Preserve them in the future model without expanding the first release. |
| 2026-07-01 | Guide before implementing application code | The owner is learning Laravel and should understand each design and implementation step. |
| 2026-07-01 | Start with username/password authentication | The first UI slice establishes sessions and role-specific dashboard access. |
| 2026-07-01 | Keep one CSS entrypoint initially | Tailwind and a shared layout are sufficient until repeated custom styling justifies a split. |

## Progress Log

- [x] Initialize Laravel and DDEV environment.
- [x] Install Laravel-aware agent tooling.
- [x] Document repository and legacy context.
- [x] Create the development roadmap.
- [x] Define the initial authentication scope and acceptance criteria.
- [ ] Complete guided Step 1 corrections and verify the users schema.
- [ ] Implement and verify the authentication vertical slice.

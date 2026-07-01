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
- Add authentication and a minimal application layout.
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

Do not create all database tables at once. Start with a short requirements session for the first vertical slice: **staff roles, staff, users, login, and authorization**.

The first release assumes:

- students never authenticate;
- a user account belongs to one staff record;
- administrators are teachers with elevated access;
- standard teachers manage their own profile and assigned students only; and
- non-teaching personnel and amounts owed to or by them are deferred.

Before implementation, confirm whether staff can hold multiple business roles, whether every teacher requires a login, how inactive staff access should behave, and which staff fields are required at creation time.

After those answers, implement this slice in order:

1. Write acceptance criteria in this file.
2. Design and review the migrations.
3. Generate models, factories, policies, requests, controllers, and PHPUnit feature tests.
4. Build the smallest functional Blade UI.
5. Run focused tests, format code, and manually verify the workflow.
6. Update this plan with results, unresolved questions, and the next slice.

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

## Progress Log

- [x] Initialize Laravel and DDEV environment.
- [x] Install Laravel-aware agent tooling.
- [x] Document repository and legacy context.
- [x] Create the development roadmap.
- [ ] Confirm Phase 1 staff/access requirements.
- [ ] Implement and verify the first vertical slice.

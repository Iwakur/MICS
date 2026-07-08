# MICS HUB Development Plan

## Purpose

This file tracks the latest active work only.

- `README.md` is the durable project truth.
- `PLAN.md` is the current working plan.
- Keep this file focused on the current goal, blockers, recent findings, and the next controlled steps.

## Current Active Work

### Active Goal

Finalize and tag the first deployable release: one exact-version application image containing PHP-FPM and Caddy, one PostgreSQL service, and a migration-first VPS flow separate from DDEV.

### Current Containerization Push

- Added a multi-stage Dockerfile that bakes production Composer dependencies and Vite assets into one immutable image.
- Added Caddy, PHP runtime tuning, Supervisor process control, a guarded app entrypoint, and an exact-version Compose stack.
- Added a Docker environment template plus durable documentation for build, migrate, first-admin bootstrap, run, and VPS updates.
- Pull requests and pushes to `main` run Pint, Larastan, PHPUnit, dependency audits, the Vite build, PostgreSQL migration/rollback/seed verification, and Playwright. A semantic-version Git tag such as `v1.0.0` reruns the gate and publishes only the matching `ghcr.io/iwakur/mics:1.0.0`; existing versions cannot be overwritten and `latest` is never published.
- Before the first release tag is pushed, replace the existing local `v1.0.0` tag because it currently points to an older commit; wait until the release work has been committed so the tag identifies the complete artifact.
- Consolidated the undeployed schema into five migrations, removed the obsolete `lesson_amount` field, indexed foreign keys, encrypted payout card values, moved Tinker out of production dependencies, and grouped tests by responsibility.
- No code-quality, migration, browser, dependency, asset, or image-build blockers remain for `v1.0.0`.

### Teacher Dashboard

- Removed the explanatory "Current Scope" card from the teacher dashboard.
- The dashboard now uses one full-width operational card; teacher access rules and assigned-student scoping are unchanged.

### Student Charge Review

- An unchanged generated charge can be validated without an adjustment reason.
- A non-zero manual adjustment still requires an attributed reason, and validated charges remain immutable.

### Shared Button Feedback

- Primary, secondary, and danger actions now share restrained hover lift, shadow, pressed, pointer, keyboard-focus, reduced-motion, and disabled states through the central Tailwind component classes.
- Monthly Finance dashboard cards now show a persistent action label and card-level hover, press, pointer, and keyboard-focus feedback.

### Bank Reconciliation Validation

- A missing reason for a non-zero bank variance now returns to the reconciliation form with a field validation message and preserved input instead of rendering an unhandled 422 exception page.

### Monthly Draft Naming

- User-facing English and Ukrainian copy now calls the existing close operation "Draft Generation": it generates financial drafts and locks operational inputs; reopening is presented as unlocking inputs for correction.
- Internal database states and service method names remain `open`/`closed` because behavior and persistence have not changed.

### Chronological Month Integrity

- Generating a selected month now also generates every earlier existing editable billing month, oldest first, in the same transaction.
- Months with no saved billing activity are not invented automatically.
- Only the latest locked month can be unlocked, preventing later debt openings and financial snapshots from resting on mutable earlier inputs.
- An inconsistent older editable month beneath a locked later month returns a normal validation error instead of silently rewriting the balance chain.
- Every joined student receives a derived monthly ledger row during generation, so paused or inactive students keep carrying prior debt even without lesson activity.
- Regeneration clears obsolete unvalidated charges and salary drafts while preserving validated financial records.
- Student charges cannot be reviewed or validated until that month’s drafts have been generated.
- Bank reconciliation is chronological: older reconciliations cannot be created or reopened beneath later reconciled months.
- A reconciled bank month blocks later validation of receipts, expenses, and refunds in that period; administrators must reopen it first so expected cash can be recalculated.

### Finance UX Review

- Draft generation now lists every existing editable month affected before confirmation.
- Dashboard finance cards preserve the selected month and include summary, lesson counts, draft generation, charges, payments, expenses, and bank reconciliation.
- Core finance screens use shared English/Ukrainian translations and locale-aware dates and numbers; bank reconciliation displays expected, actual, and variance values.
- Mobile navigation is collapsed behind an accessible toggle, and critical/destructive forms use centralized confirmation behavior.

### Language and Regional Presentation

- `users.locale` stores each authenticated user's `en` or `uk` preference; English is the database and application default.
- One shared Blade layout is used for both languages. Navigation, account controls, and the teacher dashboard use Laravel translation keys rather than duplicated language-specific templates.
- The complete teacher workflow is localized: dashboard, assigned-student listing, create/edit form, lesson-count entry, flash messages, validation errors, pagination, and shared error pages.
- The authenticated account box contains the language selector. A validated endpoint updates only the current user's preference.
- Web middleware applies the saved locale on every request before controllers and views render.
- `LocalizedFormat` centralizes human-readable dates, date-times, months, and decimal numbers through PHP Intl.
- Database dates, billing month identifiers, form values, enum values, calculations, and audit data remain locale-neutral.
- All users share `Europe/Kyiv`; timezone is not a user preference.
- Remaining feature-screen copy can move incrementally to translation keys without changing routes or business logic.

### Official Application Name

- User-facing product name: `MICS HUB`.
- Database-safe identifier: `mics_hub`.
- DDEV project and hostname slug: `mics-hub`.
- Existing database table names and PHP namespaces remain unchanged because they are domain structures, not product branding.

### Removed Operations Scope

- Removed deployment, rollback, backup, restore, and release-verification scripts.
- Removed the production environment template, readiness endpoint/command/tests, trusted-proxy override, and CI deployment job.
- Removed or rewrote documentation that referenced the deleted operations tooling.
- Retained the seeder safety boundary: demo credentials and data run only in `local` and `testing` environments.

### Dashboard Month Analytics

- The selected month is carried as a `YYYY-MM` query parameter and defaults to the current month in the configured school timezone.
- Previous, next, and current-month controls change only the reporting period; they do not change or close billing months.
- Student totals use the latest `student_configurations` row effective on or before the selected month and exclude students who had not joined yet.
- The snapshot reports total, active, paused, archived, per-lesson, and plan-based students.

### Deferred Seeding Decision Gate

- Package prices are complete monthly charges; they are not calculated from lesson averages or suffixes.
- Values such as `4.5` and package per-lesson suffixes are reporting metadata only.
- The local customer ODS and BDB files are private source material, remain excluded from Git, and are never read at runtime.
- The reviewed first seed is implemented; remaining source ambiguities are recorded in `docs/SEEDING-DECISIONS.md`.
- `docs/SEEDING-CATALOG.md` is the current correction surface for the next seed rewrite pass.
- `plans.lesson_count` is now a decimal field so the source `4.5` monthly lesson average can be stored without affecting billing calculations.
- Global reference data contains one Ukrainian teaching role, two lesson rates, eight plans, and five expense categories.
- Local/testing school data contains exactly one administrator account, one teacher account for Максим Гузьо, and twelve pupils assigned to that teacher.
- Payment confirmations, card numbers, historical payments, expenses, debt, and bank snapshots are intentionally not seeded.

### Current Known Facts

- The DBML defines staff, offerings, students, monthly billing, payments, expenses, and bank snapshots.
- `users.staff_id` remains database-nullable for safe bootstrap/inactive drafts, but every active account requires active staff and the link remains one-to-one.
- Student debt is derived from monthly rows and validated payments, never persisted on `students`.
- Accounts can be linked from User Management or Staff Management. Active teachers require teaching-capable staff; administrators may link to any active staff role.
- Staff compensation has fixed and dynamic modes; dynamic amounts are derived from effective student/catalog configuration at manual month closing.
- Per-lesson students are charged from the effective lesson rate multiplied by teacher-entered lesson count.
- Plan-based students receive a draft charge during manual month closing using the effective plan rate.
- Month closing is an explicit administrator action for a selected month, not an automatic scheduled process.
- Lesson counts remain editable by the assigned teacher and administrators until that month is closed. Closing uses the count as it exists at execution time.
- Every active plan-based student contributes the plan's school charge and teacher earning for each applicable month after the plan start date.
- The calendar month containing `plan_start_at` is the first charged month, regardless of which day in that month the plan starts; no partial-month proration applies.
- Dynamic teacher compensation is provisional during the open month and becomes an editable salary draft when the administrator closes that month.
- Staff compensation must be explicit: `fixed` uses the staff salary amount, while `dynamic` is calculated from eligible student activity. Use a named compensation mode rather than inferring the mode from a nullable amount.
- Student billing mode is already explicit as `per_lesson` or `plan_based`, and administrators may change that mode for a future billing period.
- Payment validation is a separate monthly review workflow and does not gate student charge or teacher salary draft generation.
- Expenses are irregular and are entered manually through CRUD using stable administrator-managed expense categories; month closing does not invent recurring expenses.
- A dynamic teacher may have both per-lesson and plan-based students. Both sources are combined into one monthly salary draft while remaining separately visible in statistics and calculation details.

### Current Runtime Assessment

The translated schema layer is in place. Administrators and teachers can enter scoped monthly lesson counts. Administrators can close and auditably reopen a month, review and validate generated student charges and salary drafts, validate student payments, apply attributed charge adjustments, and maintain irregular manual expenses. Validated records are immutable.

## Agreed Monthly Workflow Direction

1. Teachers and administrators edit monthly lesson counts for per-lesson students while the selected month is open.
2. Closing snapshots each per-lesson student's school charge as `lesson count * lesson type school rate`.
3. The same snapshot contributes `lesson count * lesson type teacher rate` to the assigned dynamic teacher's salary draft.
4. Each active plan-based student contributes the plan's monthly school amount to the student charge and the plan's monthly teacher amount to the assigned dynamic teacher's salary draft.
5. A plan contributes its full amounts in the month containing its start date; amounts are not prorated by start day.
6. A dynamic teacher's per-lesson and plan-based earnings are summed into one salary draft with a preserved source breakdown.
7. Fixed-compensation staff receive their configured fixed salary amount instead of student-derived compensation.
8. The close-month action generates student monthly charges, staff salary drafts, and the next month's opening balances idempotently.
9. Administrators may adjust generated drafts and monthly charge values while reviewing them.
10. A closed month's lesson counts are locked. Later lesson-count edits belong to the next open month unless an administrator explicitly reopens the closed month.
11. Reopening or correcting a month must be explicit and auditable; it must not silently rewrite validated records.
12. Payment validation remains separate and only validated payments reduce student debt.
13. Expenses are created manually and categorized; only validated expenses count as real business activity.

### Manual Month Closing

- The administrator selects an open billing month and uses a protected "Close and generate drafts" action.
- Closing may happen late; all calculations use the explicitly selected billing month rather than the current calendar date.
- The interface previews student charges, teacher salary totals, fixed salaries, and carried opening balances before confirmation.
- Teacher statistics and salary details split dynamic earnings into per-lesson and plan-based components, including contributing student counts and amounts.
- Store month lifecycle state and who closed or reopened it so the lock and later corrections are auditable.
- The operation must be transactional and safe to retry without creating duplicate monthly rows or salary drafts.
- Reopening is an administrator-only recovery path. Existing validated records require explicit adjustments rather than silent regeneration.
- Reopening may regenerate or replace unvalidated drafts only. Validated charges, salaries, payments, and expenses remain unchanged; administrators record any correction explicitly so the original business record stays auditable.

### Schema and Legacy Consistency Review

- **Staff compensation mode: implemented.** Staff records explicitly distinguish `fixed` and `dynamic` compensation; fixed salaries use the staff amount while dynamic salaries use effective lesson and plan earning rates.
- **Student billing mode: already consistent.** Both the simplified DBML and current Laravel schema define `students.billing_type` with `per_lesson` and `plan_based`, plus conditional lesson-type and plan fields.
- **Changing student billing mode: implemented with effective periods.** `student_configurations` records future-effective billing changes while existing monthly records remain historical snapshots.
- **Lesson-type amounts: implemented with effective periods.** `lesson_type_rates` stores separate school charge and teacher earning amounts by effective month.
- **Plan monthly amounts: implemented with effective periods.** `plan_rates` stores separate monthly school charge and teacher earning amounts by effective month.
- **Plan repetition: compatible with the student record.** `students.plan_start_at` determines the first applicable month; while the student remains active and plan-based, the selected plan contributes its school and teacher monthly amounts on every close.
- **Manual closing: implemented and audited.** Billing-month state and lifecycle events record closing, reopening, actors, timestamps, and reasons.
- **Expenses: already broadly consistent.** The DBML has stable `expense_categories` and manual `expenses` with draft/validated states. No recurring-expense scheduler is required.

### Proposed Operational Split

1. Teachers and administrators maintain monthly lesson counts while the month is open.
2. Administrators enter and validate student payments independently.
3. The administrator previews and manually closes a selected month, generating student charges, salary drafts, and next-month opening balances.
4. Administrators maintain irregular expenses manually through categorized CRUD.
5. Administrators adjust and validate generated drafts; later source changes do not recalculate the closed month unless it is explicitly reopened.

### Confirmed Reopen Rule

- Reopening may regenerate unvalidated drafts.
- Reopening never overwrites validated records.
- Corrections to validated records use explicit manual adjustments with a reason and administrator attribution.

## Current Schema Direction

- Use Laravel foreign-key conventions and explicit deletion behavior.
- Preserve financial history when optional staff links are removed.
- Cascade student-owned monthly history when a student is intentionally deleted.
- Use PHP backed enums with portable string columns for PostgreSQL/SQLite compatibility.
- Index foreign keys, statuses, dates, and common compound filters.

## Completed Current Step

- Financial arithmetic now uses integer cents through `App\Support\Money`; no accounting total depends on binary float addition.
- Student balance propagation uses transactions and deterministic PostgreSQL row locking to protect concurrent corrections.
- Student record ownership is centralized in `StudentPolicy`, while validation depends on user capabilities rather than route naming.
- Larastan level 5, a reviewed initial baseline, a unified `composer check` command, and GitHub Actions enforce static analysis, formatting, tests, and asset builds.
- `docs/development-conventions.md` teaches safe code placement, financial invariants, lock ordering, migration evolution, and testing.

- Payments follow an explicit draft-to-validated workflow. Only validated payments reduce student debt.
- Validation records the administrator and timestamp. Validated payments cannot be edited or deleted.
- Administrators may reopen only a closed month and must provide a reason of at least 10 characters.
- Every close and reopen is retained as a lifecycle event with administrator attribution and timestamp.
- Reclosing refreshes unvalidated drafts while preserving validated student charges and salary expenses.
- Feature tests cover payment authorization, validation, immutability, reopen auditing, and protected records.
- The monthly finance summary separates charges, validated payments, outstanding debt, validated/draft salaries, and validated/draft manual expenses.
- The student debt ledger explains each selected month's opening balance, charges, validated payments, and closing balance.
- Validated payment mistakes are corrected with one or more immutable partial/full refunds linked to the original payment.
- Reversals require an administrator reason, restore student debt, and propagate corrected balances through existing future months.
- The baseline schema is consolidated into two commented MICS HUB domain migrations plus Laravel's three framework migrations; future changes must use new migrations.
- Inactive authenticated accounts are logged out immediately instead of retaining access until their session expires.
- Missing payment months inherit prior closing debt, paused students carry existing debt without receiving new charges, and student credits no longer hide other students' positive debt.
- Student charges, salaries, and expenses record validator identity and validation time; generated salary corrections require an explanation.
- `docs/codebase-guide.md` explains Laravel and MICS HUB architecture; `docs/file-reference.md` documents every maintained source file and generated/third-party boundary.
- Every maintained path is mechanically verified against the file reference; folders have an ownership map, Blade files have purpose headers, and undocumented MICS HUB PHP/test files have concise source-purpose comments.

## Quality Checklist

- [x] Add a monthly finance summary showing generated charges, validated payments, outstanding debt, validated salaries, and validated manual expenses.
- [x] Correct validated payment mistakes with linked reversal records rather than editing history.
- [x] Propagate carried student balances across existing consecutive months when a late payment is validated or a charge is adjusted.
- [x] Document that workers and scheduler processes are not required until queued or scheduled workflows are introduced.
- [ ] Run migrations against a PostgreSQL copy and verify the restore strategy.
- [ ] Run `ddev composer test`, `ddev npm run build`, and `ddev exec ./vendor/bin/pint --test` from a clean checkout.
- [x] Perform automated browser acceptance tests on desktop and mobile for admin and teacher roles.
- [ ] Replace demo passwords and confirm seeders cannot create known credentials outside local environments.

### Verification Evidence (2026-07-05)

- [x] PHPUnit covers role boundaries, CRUD, finance transitions, reconciliation, and a deterministic three-month workflow.
- [x] Larastan level 5 passes with no new findings outside the reviewed initial Laravel inference baseline.
- [x] The unified `ddev composer check` gate passes formatting, static analysis, tests, and the asset build.
- [x] Seeded route-render smoke coverage for every maintained admin and teacher page.
- [x] Pint passes on application, database, route, bootstrap, and test PHP files.
- [x] Vite build succeeds.
- [x] Composer manifest validates strictly.
- [x] Composer audit reports no known PHP dependency advisories.
- [x] npm dependency audit reports zero known vulnerabilities.
- [x] Laravel configuration, event, route, and view optimization succeeds.
- [x] A clean local PostgreSQL 17 database builds and seeds successfully from all five consolidated migrations.

### First Release Evidence (2026-07-08)

- [x] `ddev composer check` passes: Pint, Larastan level 5, 106 PHPUnit tests with 538 assertions, Composer/npm audits, and the Vite production build.
- [x] An isolated PostgreSQL 17 database passes `migrate:fresh --seed`, final-domain rollback, remigration, and idempotent reseeding.
- [x] Playwright passes all four administrator/teacher scenarios in desktop and mobile Chromium against the isolated PostgreSQL schema.
- [x] The single production image builds without development-only Tinker, contains required PHP extensions, starts PHP-FPM and Caddy, and serves bundled public assets.
- [x] Final local image size is approximately 95 MB.

### Final Product Decisions

- [ ] Decide whether the seeded stable expense categories are sufficient or administrators need expense-category CRUD before launch.
- [x] Bank reconciliation compares expected and actual close, carries actual close, and audits reopen events.
- [ ] Decide whether every admin account must be forced to link to an active teaching staff profile at creation time; the current workflow permits temporary unlinked admin accounts.
- [x] Partial/full refunds are immutable linked negative payments with cumulative over-refund protection.

### Demo Walkthrough

- [ ] Log in as admin and explain that access roles are separate from staff business roles.
- [ ] Create/link a staff account and assign a teacher role.
- [ ] Create a lesson type and plan with separate school and teacher amounts.
- [ ] Create per-lesson and plan-based students assigned to a teacher.
- [ ] Log in as teacher and enter lesson counts only for assigned students.
- [ ] Preview and close a selected month; inspect generated student charges and salary source details.
- [ ] Adjust and validate one student charge and one salary or manual expense.
- [ ] Record a student payment draft, verify it does not reduce debt, then validate it and verify that it does.
- [ ] Reopen the month with a reason, show the lifecycle audit, then reclose and verify validated records are unchanged.
- [ ] Confirm a teacher cannot access administrator finance or account-management pages.

### Operational Understanding

- A student charge is what the school expects to receive for a month.
- A payment is separate evidence of money received; it affects debt only after validation.
- Salary drafts and manual expenses become business records only after validation.
- Closing snapshots operational inputs into drafts. Reopening unlocks correction work but does not erase history.
- Validated records are immutable. Corrections use attributed adjustments or linked refunds.

## Next Controlled Steps

1. Use the persistent `feature` branch for one new-functionality change at a time and the persistent `fix` branch for one regression-tested correction at a time; merge either into `main` only through a passing pull request.
2. Resolve the final product decisions above, especially bank reconciliation and expense-category management.
3. Perform the full desktop/mobile browser walkthrough locally.

## Step Review Template

After each completed step, explain:

- goal of the step
- files touched
- Laravel concepts used
- exact behavior gained
- risks or tradeoffs introduced
- how the step was verified
- what the next step depends on

## Working Method

- Explain the Laravel concept before editing application code.
- Prefer one small change over one large batch.
- Name the Laravel concept explicitly, not just the file name.
- Connect each code change to a MICS HUB business rule.
- Challenge fragile or un-Laravel designs.
- Keep the code understandable enough that the owner can explain it afterward.

# MICS Development Plan

## Purpose

This file tracks the latest active work only.

- `README.md` is the durable project truth.
- `PLAN.md` is the current working plan.
- Keep this file focused on the current goal, blockers, recent findings, and the next controlled steps.

## Current Active Work

### Active Goal

Complete maintainability hardening, then resolve final product/hosting decisions and execute production browser, backup, and restore acceptance.

### Current Known Facts

- The DBML defines staff, offerings, students, monthly billing, payments, expenses, and bank snapshots.
- Existing authentication requires the current `users` fields and permits account creation before staff management exists.
- `users.staff_id` is therefore nullable but unique; all linked accounts remain one-to-one with staff.
- Student debt is derived from monthly rows and validated payments, never persisted on `students`.
- Accounts are created independently in User Management and linked from the staff form; only unlinked accounts are selectable.
- Staff compensation has two modes: fixed salary and a dynamic amount calculated on a configured date near month end.
- Per-lesson students are charged from lesson type price multiplied by teacher-entered lesson count.
- Plan-based students receive a draft charge on a configured billing date.
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

- **Staff compensation mode: requires an extension.** The simplified DBML and current Laravel schema have only `staff.salary_amount`; they do not explicitly distinguish fixed and dynamic compensation. The older application used nullable `fixed_salary_amount`, where teachers were calculated dynamically. The agreed implementation should add an explicit `fixed`/`dynamic` mode rather than a boolean so its meaning stays clear and can be extended later.
- **Student billing mode: already consistent.** Both the simplified DBML and current Laravel schema define `students.billing_type` with `per_lesson` and `plan_based`, plus conditional lesson-type and plan fields.
- **Changing student billing mode: structurally possible but needs effective-period rules.** Updating the student row can change future behavior, while `student_months` preserves already-created monthly amounts. The application must prevent a mode change from rewriting an existing draft or validated month.
- **Lesson-type amounts: requires an extension.** Current `lesson_types.lesson_price` can represent the school's per-lesson charge, but a separate per-lesson teacher earning amount is missing.
- **Plan monthly amounts: requires clearer fields.** Current `plans.plan_price` can represent the school's monthly plan charge, but a separate monthly teacher earning amount is missing. These should be named explicitly so they are not confused with lesson metadata.
- **Plan repetition: compatible with the student record.** `students.plan_start_at` determines the first applicable month; while the student remains active and plan-based, the selected plan contributes its school and teacher monthly amounts on every close.
- **Manual closing: requires a month lifecycle record.** `student_months` provides per-student history, but the schema does not currently record whether the whole month is open or closed, who closed it, or whether it was reopened.
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
- `docs/development-conventions.md` teaches safe code placement, financial invariants, lock ordering, migration evolution, testing, and atomic release practices.

- Payments follow an explicit draft-to-validated workflow. Only validated payments reduce student debt.
- Validation records the administrator and timestamp. Validated payments cannot be edited or deleted.
- Administrators may reopen only a closed month and must provide a reason of at least 10 characters.
- Every close and reopen is retained as a lifecycle event with administrator attribution and timestamp.
- Reclosing refreshes unvalidated drafts while preserving validated student charges and salary expenses.
- Feature tests cover payment authorization, validation, immutability, reopen auditing, and protected records.
- The monthly finance summary separates charges, validated payments, outstanding debt, validated/draft salaries, and validated/draft manual expenses.
- The student debt ledger explains each selected month's opening balance, charges, validated payments, and closing balance.
- Validated payment mistakes are corrected with one immutable full reversal linked to the original payment.
- Reversals require an administrator reason, restore student debt, and propagate corrected balances through existing future months.
- Production deployments have a secret-free environment template, explicit trusted-proxy configuration, liveness/readiness endpoints, and a configuration gate command.
- `docs/deployment.md` defines deployment order, PostgreSQL backup/restore testing, process requirements, rollback boundaries, and release verification.
- The pre-release schema is consolidated into two commented MICS domain migrations plus Laravel's three framework migrations; future post-launch changes must use new migrations.
- Inactive authenticated accounts are logged out immediately instead of retaining access until their session expires.
- Missing payment months inherit prior closing debt, paused students carry existing debt without receiving new charges, and student credits no longer hide other students' positive debt.
- Student charges, salaries, and expenses record validator identity and validation time; generated salary corrections require an explanation.
- `docs/codebase-guide.md` explains Laravel and MICS architecture; `docs/file-reference.md` documents every maintained source file and generated/third-party boundary.
- Every maintained path is mechanically verified against the file reference; folders have an ownership map, Blade files have purpose headers, and undocumented MICS PHP/test files have concise source-purpose comments.

## Deployment Readiness Checklist

### Required Before Production

- [x] Add a monthly finance summary showing generated charges, validated payments, outstanding debt, validated salaries, and validated manual expenses.
- [x] Correct validated payment mistakes with linked reversal records rather than editing history.
- [x] Propagate carried student balances across existing consecutive months when a late payment is validated or a charge is adjusted.
- [ ] Add production environment values for `APP_URL`, database, mail, queue, cache, session, and trusted proxy settings without committing secrets.
- [ ] Configure HTTPS, secure cookies, database backups, retention, and a tested restore procedure.
- [x] Document that workers and scheduler processes are not required until queued or scheduled workflows are introduced.
- [ ] Run migrations against a production-like PostgreSQL copy and verify rollback/restore strategy.
- [ ] Run `ddev composer test`, `ddev npm run build`, and `ddev exec ./vendor/bin/pint --test` from a clean checkout.
- [ ] Perform browser acceptance tests on desktop and mobile for admin and teacher roles.
- [ ] Replace demo passwords and confirm seeders cannot create known credentials in production.

### Verification Evidence (2026-07-05)

- [x] Full PHPUnit suite: 82 tests, 374 assertions.
- [x] Larastan level 5 passes with no new findings outside the reviewed initial Laravel inference baseline.
- [x] The unified `ddev composer check` gate passes formatting, static analysis, tests, and production asset build.
- [x] Seeded route-render smoke coverage for every maintained admin and teacher page.
- [x] Pint passes on application, database, route, bootstrap, and test PHP files.
- [x] Production Vite build succeeds.
- [x] Composer manifest validates strictly.
- [x] Composer audit reports no known PHP dependency advisories.
- [x] npm production audit reports zero known vulnerabilities.
- [x] Laravel configuration, event, route, and view optimization succeeds.
- [x] A clean local PostgreSQL 17 database builds and seeds successfully from all five consolidated migrations.
- [x] `/up`, `/ready`, and the production-readiness command have automated coverage.

### Final Product Decisions

- [ ] Decide whether the seeded stable expense categories are sufficient or administrators need expense-category CRUD before launch.
- [ ] Decide whether the existing `bank_months` data model needs an administrator reconciliation screen for the first release.
- [ ] Decide whether every admin account must be forced to link to an active teaching staff profile at creation time; the current workflow permits temporary unlinked admin accounts.
- [ ] Decide whether partial refunds need a separate workflow; current correction uses full reversal followed by a replacement payment.

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
- Validated records are immutable. Corrections must be represented as attributed adjustments or future reversal records.

## Next Controlled Steps

1. Merge the tested release candidate into protected `main` through a pull request and tag the deployed commit as `v1.0.0`.
2. The project owner will configure and operate the VPS, including secrets, HTTPS, backups, deployment, and rollback.
3. Continue new work on short-lived `feature/<topic>`, `fix/<topic>`, or `docs/<topic>` branches created from current `main`.
4. Resolve the final product decisions above, especially bank reconciliation and expense-category management.
5. Perform the full desktop/mobile browser walkthrough against the production-like deployment.

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
- Connect each code change to a MICS business rule.
- Challenge fragile or un-Laravel designs.
- Keep the code understandable enough that the owner can explain it afterward.

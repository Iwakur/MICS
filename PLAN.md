# MICS Development Plan

## Purpose

This file tracks the latest active work only.

- `README.md` is the durable project truth.
- `PLAN.md` is the current working plan.
- Keep this file focused on the current goal, blockers, recent findings, and the next controlled steps.

## Current Active Work

### Active Goal

Build the next operational CRUD modules on top of the already-translated schema, starting with staff and then moving into student workflow.

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

The translated schema layer is in place. Admin staff CRUD includes explicit fixed/dynamic compensation, and business roles are managed through an archive-safe staff-role catalog with explicit teaching capability. Student CRUD enforces per-lesson versus plan-based configuration and permits assignment only to active teaching-capable staff. Administrators control assignment and archive state; teachers can create and edit only students assigned to their linked active teaching profile.

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

## Next Controlled Steps

1. Add auditable month lifecycle records.
2. Add teacher-owned monthly lesson-count entry with authorization scoped to assigned students.
3. Add idempotent manual month closing and salary-source snapshots.

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

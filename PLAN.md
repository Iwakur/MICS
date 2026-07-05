# MICS Development Plan

## Purpose

This file tracks the latest active work only.

- `README.md` is the durable project truth.
- `PLAN.md` is the current working plan.
- Keep this file focused on the current goal, blockers, recent findings, and the next controlled steps.

## Current Active Work

### Active Goal

Implement the latest MICS DBML as a Laravel-native relational domain model with migrations, Eloquent relationships, factories, and database behavior tests.

### Current Known Facts

- The DBML defines staff, offerings, students, monthly billing, payments, expenses, and bank snapshots.
- Existing authentication requires the current `users` fields and permits account creation before staff management exists.
- `users.staff_id` is therefore nullable but unique; all linked accounts remain one-to-one with staff.
- Student debt is derived from monthly rows and validated payments, never persisted on `students`.

### Current Runtime Assessment

All domain migrations, models, factories, and schema behavior tests have been added. A clean migration and seed passed on PostgreSQL 17, and the complete suite passes on both the local SQLite test configuration and DDEV.

## Current Schema Direction

- Use Laravel foreign-key conventions and explicit deletion behavior.
- Preserve financial history when optional staff links are removed.
- Cascade student-owned monthly history when a student is intentionally deleted.
- Use PHP backed enums with portable string columns for PostgreSQL/SQLite compatibility.
- Index foreign keys, statuses, dates, and common compound filters.

## Next Controlled Steps

1. Review and approve the implemented schema and deletion rules.
2. Design the staff-management workflow before making `users.staff_id` mandatory.
3. Add request validation for per-lesson versus plan-based student fields when student CRUD is implemented.
4. Build the first domain CRUD surface, preferably staff before students so account linking has a controlled workflow.

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

# MICS Development Plan

## Purpose

This file tracks the latest active work only.

- `README.md` is the durable project truth.
- `PLAN.md` is the current working plan.
- Keep this file focused on the current goal, blockers, recent findings, and the next controlled steps.

## Current Active Work

### Active Goal

Repair the authentication foundation so the auth layer is internally consistent before building login routes and views.

### Current Known Blockers

- `app/Models/User.php` and `database/factories/UserFactory.php` must match the current `users` migration exactly.
- We still need runtime verification from DDEV after each PHP step because this Codex shell cannot run `ddev` directly.

### Current Runtime Assessment

Laravel already boots correctly in DDEV. The current issue is not framework bootstrap. The current issue is auth foundation consistency.

## Current Auth Schema Reality

The current `users` migration already defines:

- `username` as unique
- `email` as unique
- `email_verified_at` as nullable
- `password`
- `role` defaulting to `teacher`
- `is_active` defaulting to `true`
- `remember_token`
- timestamps

So the current problem is not the migration first. The current problem is consistency between migration, model, enum, and factory.

## Next Controlled Steps

1. Create `app/Enums/UserRole.php` with the real backed enum used by the model.
2. Update `app/Models/User.php` so fillable fields and casts match the current schema.
3. Update `database/factories/UserFactory.php` so generated test data matches the same schema.
4. Run DDEV verification for the auth foundation.
5. Only after that should we continue to login routes, controllers, and views.

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

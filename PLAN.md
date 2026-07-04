# MICS Development Plan

## Purpose

This file tracks the latest active work only.

- `README.md` is the durable project truth.
- `PLAN.md` is the current working plan.
- Keep this file focused on the current goal, blockers, recent findings, and the next controlled steps.

## Current Active Work

### Active Goal

Replace the temporary dashboard with a real authenticated shell and add the first safe admin CRUD screen for user management.

### Current Known Facts

- Login and logout already work with `username` and `password`.
- Users already have the core fields needed for administration:
  - `username`
  - `email`
  - `password`
  - `role`
  - `is_active`
- The first admin management screen should respect database uniqueness rules and the role model.
- The system must never lose its last active administrator.

### Current Runtime Assessment

Laravel boots correctly in DDEV. The active work is now application structure and admin-safe user management, not auth bootstrap.

## Current UI Direction

The new authenticated area now moves toward a reusable app shell:

- shared header
- shared left sidebar
- separate admin and teacher dashboards
- first admin CRUD screen for users
- role-aware route flow from the generic `/dashboard`

This makes the application shape understandable before deeper school-domain screens are added.

## Next Controlled Steps

1. Add a shared authenticated layout for staff pages.
2. Redirect `/dashboard` to the correct role-specific dashboard.
3. Add an admin-only user-management screen.
4. Enforce safe rules when changing or deleting admin accounts.
5. Add feature tests for role routing and admin CRUD safety.
6. Verify the new screens in DDEV and then choose the next MICS domain screen.

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

# MICS Development Plan

## Purpose

This file tracks the latest active work only.

- `README.md` is the durable project truth.
- `PLAN.md` is the current working plan.
- Keep this file focused on the current goal, blockers, recent findings, and the next controlled steps.

## Current Active Work

### Active Goal

Apply the teaching-focused documentation pass and the blue-dark UI consistency pass to the current authenticated application surface.

### Current Known Facts

- The current visible app surface is now login + shared authenticated shell + admin dashboard + teacher dashboard + admin user CRUD.
- The username/password auth flow, role-based dashboard redirect, and admin CRUD safety rules must not change during this pass.
- The frontend should not include rescue styling for broken Vite or npm situations.
- The repo now has both durable docs (`README.md`) and a deeper codebase walkthrough (`docs/codebase-guide.md`).

### Current Runtime Assessment

The remaining work is presentation and explanation quality, not new domain behavior. Runtime verification in DDEV is still required after the visual and comment pass.

## Current Documentation/UI Direction

This pass is intentionally about understanding and consistency:

- explicit teaching comments in the files we created or materially changed
- clearer durable docs for repo structure and request flow
- one shared blue-dark visual language across the visible app
- no fallback styling for asset-pipeline failure cases

## Next Controlled Steps

1. Verify the updated screens with built assets in DDEV.
2. Run the focused auth, dashboard, and admin CRUD tests again.
3. Review whether the old temporary `resources/views/dashboard.blade.php` should be removed in a cleanup pass.
4. Choose the first real business module after user management, most likely students.

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

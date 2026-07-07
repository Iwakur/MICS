# MICS HUB Development Conventions

## Why This File Exists

This is the practical decision guide for changing MICS HUB. Read `docs/codebase-guide.md` first to understand Laravel's request lifecycle, then use this file while implementing a change. `README.md` remains the product truth and `PLAN.md` remains the active work list.

## Safe Change Workflow

1. Write the business rule in `PLAN.md`, including who may perform it and whether it changes historical financial records.
2. Find the existing route, controller, Form Request, model, service, policy, view, and feature test for the nearest workflow.
3. Add or update a failing test that describes externally visible behavior.
4. Make the smallest implementation that passes the test. Keep request coordination in controllers and reusable business calculations in services or small domain methods.
5. Run `ddev composer check`. Review the diff and migration SQL before committing.

Use `feature` for new behavior and `fix` for defects. A defect starts with a failing regression test. Every schema or workflow change updates tests, DBML, relevant guides, and `PLAN.md` in the same pull request. Exact commands are in `docs/commands.md`.

Small and large changes use the same process. Split large changes into compatible steps.

## Where Code Belongs

- Routes map URLs and middleware to controllers. They do not contain business calculations.
- Middleware performs broad request checks, such as authentication, active account, or administrator area access.
- Policies answer record-level questions, such as whether a teacher owns a student.
- Form Requests authorize the submitted action, validate input, and normalize trusted data.
- Controllers coordinate one HTTP request and redirect or render a view.
- Services own reusable multi-model workflows and transaction boundaries.
- Models define table mapping, casts, relationships, query scopes, and small calculations about one model.
- Blade views display prepared data. They do not query the database or calculate accounting values.
- Feature tests verify routes, permissions, validation, persistence, and workflows. Unit tests verify isolated calculations such as money conversion.

## Financial Rules

- Database money columns remain `decimal(12, 2)` strings. Calculations must use `App\Support\Money` integer cents, never PHP float arithmetic.
- A student charge is an amount owed. A payment is separate evidence of receipt and affects debt only after validation.
- Validated financial records are immutable. Correct them through an attributed adjustment or linked refund, not an overwrite.
- Student debt is derived from monthly snapshots and validated payments. Never add a mutable `students.debt` column.
- Closing a month snapshots current inputs. It must be transactional, idempotent, and preserve validated records when a month is reopened.

## Transactions and Locking

Use a database transaction when multiple writes must succeed or fail together. For balance-changing workflows, lock rows in this order:

1. `students` rows in ascending ID order.
2. Related `student_months` rows in month order.
3. Related payment or expense row when required.

`StudentBalanceService` is the single place for creating missing student months and propagating opening balances. Do not manually update a later opening balance from a controller. PostgreSQL enforces these locks; SQLite tests verify behavior but cannot fully simulate concurrent requests.

## Authorization

- Authentication proves who the user is.
- `active` middleware rejects disabled accounts.
- `admin` middleware protects the administrator URL group.
- Policies protect access to a specific model record.
- Form Requests protect state transitions and submitted fields.
- Views may hide unavailable buttons for clarity, but hidden UI is never the security boundary.

Access role and staff role are intentionally separate. `users.role` controls application access. `staff.staff_role_id` describes the person's business role. An administrator can also be linked to a teaching staff profile.

## Database Migrations

The two consolidated MICS HUB migrations are the baseline. Never edit an already-applied migration. Create a new migration for every schema change.

Use the expand-and-contract pattern for risky changes:

1. Add a nullable column/table/index without removing the old structure.
2. Update code to understand both structures.
3. Backfill and verify existing data.
4. Switch all reads and writes to the new structure.
5. Remove the old structure in a later release only after rollback is no longer required.

Never mix irreversible data correction with an unrelated schema migration. Back up PostgreSQL and test restoration before a financial-schema release.

Effective-dated student configuration and catalog rates preserve historical calculations. New period rows begin in an open month after the latest closed month. Month closing must never read only the latest mutable catalog/student values.

## Tests and Quality Gates

Run the complete local gate:

```bash
ddev composer check
```

It checks Pint formatting, Larastan static analysis, PHPUnit behavior, and the Vite build. GitHub Actions repeats these checks for pushes and pull requests.

Use `ddev composer test:workflow` after changes to closing, balances, effective configuration, payments/refunds, expenses, or reconciliation. Playwright verifies real-browser smoke behavior; PHPUnit remains the detailed business-rule suite.

`phpstan-baseline.neon` contains reviewed Laravel dynamic-property inference debt present when static analysis was introduced. Do not add entries casually. Fix the type or relationship annotation first; if an exception is unavoidable, document why and keep it narrowly matched. Reduce the baseline over time.

Every bug fix needs a regression test. Every permission change needs allowed and denied cases. Every accounting change needs exact expected amounts, including cents and correction behavior.

## Comments and Documentation

Prefer names and small methods over comments that restate code. Add comments for business invariants, non-obvious lock ordering, security decisions, and framework behavior that would surprise a maintainer.

Update documentation as part of the same change:

- `README.md` for durable product behavior.
- `PLAN.md` for active work and unresolved decisions.
- `docs/schema.dbml` after a migration changes the application schema; migrations remain authoritative.
- `docs/file-reference.md` when files are added, removed, or responsibilities change.

## Git and Release Workflow

- `main` contains reviewed code. Protect it, require CI, and update it only through reviewed pull requests from `feature` or `fix`.
- `feature` contains new functionality that is not yet released. Start feature work here, keep it synchronized with `main`, and open a pull request from `feature` into `main` when the complete change passes the quality gate.
- `fix` contains corrections to existing behavior. Start bug-fix work here, add a regression test, keep it synchronized with `main`, and open a pull request from `fix` into `main` when the correction passes the quality gate.
- After merging either working branch, update it from `main` before beginning unrelated work. Do not merge `feature` into `fix` or `fix` into `feature`; `main` is their shared integration point.
- Only one coherent change should be active on each working branch at a time. If concurrent feature or fix work becomes necessary, replace this three-branch model with short-lived topic branches such as `feature/<topic>` and `fix/<topic>`.

## Learning Checklist

Before implementing a feature, be able to answer:

1. What business rule changes?
2. Which user role may do it, and which records may they access?
3. Which table owns the source of truth?
4. Is historical data mutable, draft-only, or immutable?
5. Does the change require a transaction or row lock?
6. Which feature test proves the result and which denied test proves security?
7. Can the change be delivered in smaller compatible steps?

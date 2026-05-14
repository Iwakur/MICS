# MICS

MICS is a plain-PHP school operations system built on PostgreSQL.

The goal is to keep the project ready for real use, but still simple:

- server-rendered PHP pages
- separate admin and teacher areas
- simple CRUD-first workflow
- monthly student balance tracking
- simple payments and expenses
- no heavy accounting system
- no debit/credit journal
- no unnecessary framework complexity

This README is the project orientation document. It explains:

- what the project is
- how the database is meant to work
- why the financial model is structured this way
- what folders the new project should have

## 1. Project Idea

This project is for school/academy operations.

The main business areas are:

- staff and teacher management
- user login and access
- student management
- lesson types and plans
- monthly student debt tracking
- payment review and validation
- expense review and validation
- simple monthly bank closing

The project should stay simpler than `MICS_legacy`.

That means:

- fewer files
- less architectural layering
- more direct CRUD
- no full accounting subsystem
- no silent background business logic hidden in bootstrap

## 2. Core Product Rules

These are the main rules currently reflected in the schema design.

### Users and staff

- every `user` belongs to exactly one `staff` row
- `staff.is_active` is the source of truth for whether that person is active
- `user.role` is a system access role such as `admin` or `teacher`
- `staff_roles` is business metadata such as teacher, manager, assistant

Why both `user.role` and `staff_role_id` exist:

- `user.role` controls access
- `staff_role_id` describes the person in the business

Those are related, but not the same concept.

### Students

Each student stores:

- personal/contact data
- assigned staff member
- status
- billing type
- optional lesson type
- optional plan
- discount

Important rule:

- current debt is **not** stored directly on `students`

Why:

- debt is a result of history
- if stored directly, it can go out of sync
- monthly rows give better traceability

### Monthly balance model

The financial core of the project is `student_months`.

One row represents one student in one month.

Fields:

- `opening_balance`
- `charge_amount`
- `manual_adjustment`

Meaning:

- `opening_balance` = debt carried from previous month
- `charge_amount` = what this month adds
- `manual_adjustment` = correction that does not fit normal charge/payment flow

Examples of `manual_adjustment`:

- admin correction
- debt forgiveness
- extra charge
- prize/credit reinvested for the student

Current month result:

```text
closing_balance = opening_balance + charge_amount + manual_adjustment - validated_payments
```

Next month rule:

- next row's `opening_balance` should equal previous month's calculated closing balance

This gives:

- debt history
- continuity between months
- simpler logic than a full accounting system

### Payments

Payments belong to `student_months`, not directly to students.

That means:

- every payment is attached to a specific month row
- there is no ambiguity about which month a payment belongs to

Payments use status:

- `draft`
- `validated`

Meaning:

- `draft` = proposed/imported/review-pending and should not affect real totals
- `validated` = accepted as real money received

Important rule:

- only validated payments affect debt calculations

### Expenses

Expenses are intentionally simple.

They support:

- manual creation
- future automatic creation
- future import if needed

Expense fields include:

- optional linked staff member
- expense category
- month marker
- amount
- status
- auto-generated flag

Expense statuses:

- `draft`
- `validated`

Meaning:

- `draft` = system-created or not yet reviewed
- `validated` = reviewed and accepted as real

If an expense row is wrong, it can simply be deleted instead of introducing extra accounting states.

### Bank month closing

`bank_months` is a simple monthly bank snapshot table.

It is not a full bank ledger and not accounting.

Purpose:

- keep a monthly opening amount
- keep a monthly closing amount
- help close and compare months simply

The month field is a `date`, but used as a month marker.

Convention:

- `2026-05-01` means May 2026
- `2026-06-01` means June 2026

## 3. Database Overview

Current schema file:

- [schema.dbml](/C:/laragon/www/GitHub/MICS/database/schema.dbml)

### Main tables

- `staff`
- `users`
- `students`
- `student_months`
- `payments`
- `expenses`
- `bank_months`

### Lookup/meta tables

- `staff_roles`
- `lesson_types`
- `plans`
- `expense_categories`

### Enums

- `user_role`
- `student_status`
- `student_billing_type`
- `payment_status`
- `expense_status`

Why enums are used only here:

- these are small and stable system states
- editable business metadata is kept in normal tables instead

## 4. Table Meanings

### `staff`

Business people in the system.

Examples:

- teacher
- manager
- assistant

### `users`

Login accounts for staff members.

This is access/auth data, not business profile data.

### `students`

Main student record.

This stores the student's identity and billing setup, not the rolling debt history.

### `student_months`

Monthly balance record per student.

This is the key table for debt tracking.

### `payments`

Money received for a specific `student_month`.

Can exist in draft state until reviewed.

### `expenses`

Money the business pays out.

Can also exist in draft state until reviewed.

### `bank_months`

Simple month-level bank opening/closing numbers.

## 5. Time Fields

The project uses two kinds of time fields.

### Business dates

These describe business reality:

- `birthday`
- `joined_at`
- `plan_start_at`
- `month_date`

Why `date` is used:

- only the calendar date matters
- no hour/minute is needed

### Exact event timestamps

These describe exact events:

- `created_at`
- `updated_at`
- `last_login_at`
- `paid_at`

Why `timestamptz` is used:

- safer for real event timestamps
- timezone-aware behavior in PostgreSQL

Important:

- `timestamptz` is not automatically "Ukraine time"
- timezone behavior should be configured later in PostgreSQL and PHP

## 6. Why This Is Simpler Than Legacy

Compared to `MICS_legacy`, this version intentionally avoids:

- accounting journal tables
- debit/credit posting logic
- statement import subsystem in v1
- route/file explosion
- overly layered service/repository structure everywhere

What is kept from the good parts of legacy:

- plain PHP
- server-rendered views
- split admin and teacher areas
- PostgreSQL
- explicit schema design
- clear business entities

## 7. Proposed Project Structure

The project should stay plain PHP, but be cleaner than the legacy layout.

Recommended structure:

```text
MICS/
├─ README.md
├─ .env
├─ .env.example
├─ .gitignore
├─ public/
│  ├─ index.php
│  └─ assets/
│     ├─ css/
│     ├─ js/
│     └─ images/
├─ app/
│  ├─ bootstrap.php
│  ├─ config/
│  ├─ support/
│  ├─ auth/
│  ├─ admin/
│  ├─ teacher/
│  ├─ modules/
│  │  ├─ staff/
│  │  ├─ users/
│  │  ├─ students/
│  │  ├─ lesson-types/
│  │  ├─ plans/
│  │  ├─ student-months/
│  │  ├─ payments/
│  │  ├─ expenses/
│  │  ├─ expense-categories/
│  │  ├─ staff-roles/
│  │  └─ bank-months/
│  └─ views/
│     ├─ layouts/
│     ├─ partials/
│     ├─ admin/
│     └─ teacher/
├─ database/
│  ├─ schema.dbml
│  └─ schema.sql
├─ docs/
│  ├─ architecture.md
│  └─ database-notes.md
└─ storage/
   └─ uploads/
```

## 8. Why This Structure

### `public/`

Web root.

Put public files here:

- front controller
- CSS
- JS
- images

This is cleaner and safer than exposing the whole project root.

### `app/`

Main PHP code.

### `app/auth/`

Login, logout, session checks, role gates.

### `app/admin/` and `app/teacher/`

Role-specific entry points/controllers.

Keep these because you want two separate app areas.

### `app/modules/`

Business modules live here.

This is the most important structure decision.

Instead of scattering student logic across many unrelated folders, group it by business domain.

Example:

- student queries
- student validation
- student actions

should stay close together inside `modules/students/`.

This is cleaner than the legacy "route files + repositories + services everywhere" approach.

### `app/views/`

Server-rendered templates.

Keep shared layout pieces separated:

- `layouts/`
- `partials/`

This matches your preference for separate sidebar/header pieces.

### `database/`

Schema files only.

- `schema.dbml` for design
- `schema.sql` for executable PostgreSQL schema

### `docs/`

Deeper project docs.

Recommended:

- `architecture.md`
- `database-notes.md`

The root README should stay the main orientation file.

## 9. File Philosophy

The project should avoid unnecessary file explosion.

Good:

- clear modules
- shared layout partials
- reusable helpers where they actually help

Bad:

- one tiny file for every trivial action if it adds noise
- layers added just because enterprise apps do that
- duplicated business rules in many places

Rule of thumb:

- keep code explicit
- abstract only repeated logic with clear value

## 10. Next Build Order

Recommended order:

1. finalize `schema.dbml`
2. convert it into `database/schema.sql`
3. define the route map
4. define auth/bootstrap structure
5. implement core CRUD in this order:
   - staff roles
   - staff
   - users
   - expense categories
   - lesson types
   - plans
   - students
   - student months
   - payments
   - expenses
   - bank months
6. build admin area
7. build teacher area

## 11. Current Status

Current workspace contains:

- the new DBML draft in `database/`
- the old reference project in `MICS_legacy/`

The new project should use `MICS_legacy` only as reference, not as structure to copy directly.

# MICS Hub

MICS Hub is a plain-PHP internal school operations system that combines:

- authentication and role-gated access
- student and staff CRM workflows
- starter accounting/journal structure
- PostgreSQL-backed data storage
- separate admin and teacher workspaces

This README is intended to work as project documentation, not just setup notes. It explains:

- what the app does
- how requests flow through the code
- how folders and files are organized
- what database tables mean
- which modules are already usable
- what is still only starter scaffolding

---

## 1. Current State

What is already usable now:

- login / logout
- admin dashboard
- teacher dashboard
- admin student CRUD
- admin staff CRUD
- admin users CRUD
- admin statement import and payment-draft review hub
- admin SQL console
- teacher own-students list
- teacher create student
- teacher edit own student
- teacher payouts page
- teacher profile page
- automatic database provisioning on first request

What is still starter-level or placeholder-only:

- expenses
- accounts UI
- journal UI
- deeper accounting posting automation

So the app is no longer just a shell. The CRUD base is implemented, and the bootstrap is now intentionally minimal instead of being filled with demo operational data.

---

## 2. High-Level Architecture

The system has four functional layers.

### Access layer

- `users`

Purpose:

- login credentials
- role assignment
- active/inactive access control
- required link from every user to a `staff` row

### Master data layer

- `staff`
- `students`
- `plans`
- `expense_categories`

Purpose:

- core records the rest of the app depends on

### Business layer

- `student_charges`
- `payments`
- `statement_import_batches`
- `statement_import_rows`
- `expenses`
- `staff_payouts`

Purpose:

- operational events and source documents

### Accounting layer

- `accounts`
- `journal_entries`
- `journal_entry_lines`

Purpose:

- financial truth in debit/credit form

Mental model:

- business tables explain what happened
- journal tables explain how it affects the books

---

## 3. Roles and Access

The app currently supports two roles:

- `admin`
- `teacher`

### Admin

Admin pages live under:

- `admin/*.php`

Admins currently have working CRUD over:

- students
- staff
- users

Admins also have starter pages for:

- payments
- expenses
- payouts
- accounts
- journal
- SQL console

### Teacher

Teacher pages live under:

- `teacher/*.php`

Teachers currently can:

- view their own dashboard
- view only their own students
- create a student assigned to themselves
- edit only their own students
- archive/unarchive their own students by status
- review their payout suggestion/history
- manage their own profile password

Teacher routes require:

- role = `teacher`
- linked `staff_id`

That rule is enforced in PHP before the page renders.

---

## 4. Request Flow

This project does not use Laravel, Symfony, or another full framework.
It uses a direct plain-PHP page-entry structure.

Typical request flow:

1. browser opens a route such as `admin/students.php`
2. that file includes `app/bootstrap.php`
3. bootstrap loads initialization and starts the PHP session
4. bootstrap verifies the database is ready
5. the page runs its auth gate
6. the page handles GET or POST
7. repository/service classes handle data access or validation
8. the page renders a view through a layout

That means each route file acts like a small controller.

Example:

- `admin/students.php`
  - checks admin access
  - handles status update POST
  - loads search/filter GET data
  - fetches list data from `StudentRepository`
  - renders `views/admin/students.php` with the admin layout

---

## 5. Bootstrap and Initialization

There are three important bootstrap files.

### `app/init.php`

Purpose:

- define `BASE_PATH`
- load helpers
- register the `App\...` autoloader
- set the PHP timezone from config

### `app/bootstrap.php`

Purpose:

- require `app/init.php`
- start PHP session
- call `App\DatabaseProvisioner::ensureReady()`

This is the shared bootstrap used by route files.

### `app/DatabaseProvisioner.php`

Purpose:

- verify PostgreSQL extension exists
- verify schema readiness
- create the database if missing
- run `database/schema.sql`
- seed required base bootstrap data
- create upload directories

This is why the app can provision itself on first web request.

The permanent base bootstrap is intentionally small:

- chart of accounts
- expense categories
- one admin staff row
- one teacher staff row
- one admin user linked to admin staff
- one teacher user linked to teacher staff

It does not auto-create plans, students, statement imports, or other demo operational rows.

---

## 6. Authentication and Sessions

Authentication logic lives in:

- `app/Auth.php`

What it does:

- check credentials against `users`
- store logged-in user data in `$_SESSION['auth_user']`
- enforce guest/admin/teacher access
- reject invalid teacher sessions without a linked `staff_id`
- log out users

Important distinction:

- PHP session stores the currently logged-in app user
- PostgreSQL session is the database connection session

They are not the same concept.

---

## 7. URL Building and Path Helpers

Helpers live in:

- `app/helpers.php`

Important functions:

- `base_path()` -> filesystem path builder
- `config()` -> nested config reader
- `app_url()` -> app-relative browser URL builder
- `asset_url()` -> asset URL builder
- `redirect()` -> HTTP redirect helper
- `flash()` -> flash storage
- `old()` -> previously submitted form values
- `form_errors()` / `field_error()` -> form validation helpers
- `render()` -> view + layout renderer

### URL rule

URLs are built from:

- `config.php`
- `app.base_path`

For this project the base path is:

```text
/MICS
```

So:

- `app_url('login.php')` -> `/MICS/login.php`
- `asset_url('css/app.css')` -> `/MICS/assets/css/app.css`

---

## 8. Folder and File Map

This section maps the project as it exists now.

## Root files

### `config.php`

Central configuration file.

Contains:

- app name
- app base path
- app timezone
- database host / port / db name / user / password

### `index.php`

Root entry route.

Purpose:

- bootstrap app
- redirect guest to login
- redirect logged-in user to correct dashboard by role

### `login.php`

Login route/controller.

Purpose:

- guest-only page
- validate CSRF
- call `Auth::attempt()`
- redirect after login
- render login form on GET

### `logout.php`

Logout route.

Purpose:

- clear auth session
- return user to login flow

### `schema.dbml`

Schema documentation in DBML form.

Purpose:

- structural documentation
- relationship documentation
- design-level overview

### `README.md`

This file.

Purpose:

- project documentation
- setup documentation
- codebase orientation

---

## `app/`

Core application logic.

### `app/Auth.php`

Authentication and role-gating logic.

### `app/bootstrap.php`

Shared request bootstrap.

### `app/Database.php`

Runtime PDO connection factory.

Purpose:

- open PostgreSQL connection
- apply DB session settings such as timezone
- expose shared connection instance

### `app/DatabaseProvisioner.php`

Automatic setup/provisioning logic.

### `app/helpers.php`

Global helper functions used by route files and views.

### `app/init.php`

Low-level startup file shared by bootstrap and setup script.

### `app/Repositories/`

Read/write data access classes.

Current repository files:

- `DashboardRepository.php`
- `JournalRepository.php`
- `PayoutRepository.php`
- `SqlConsoleRepository.php`
- `StaffRepository.php`
- `StudentRepository.php`
- `TeacherProfileRepository.php`
- `UserRepository.php`

Purpose of repositories:

- keep SQL out of views
- keep route files thinner
- centralize query logic

### `app/Services/`

Business/service layer classes.

Current service files:

- `AccountingService.php`
- `PayoutService.php`
- `SqlConsoleService.php`
- `StaffFormService.php`
- `StudentFormService.php`
- `TeacherPasswordService.php`
- `UserFormService.php`

Purpose of services:

- form validation and normalization
- accounting posting logic
- non-trivial workflow behavior

---

## `admin/`

Admin route/controller entry files.

Each file is a route, not just a template.

Current files:

- `dashboard.php`
- `students.php`
- `student-create.php`
- `student-edit.php`
- `staff.php`
- `staff-create.php`
- `staff-edit.php`
- `payments.php`
- `expenses.php`
- `payouts.php`
- `accounts.php`
- `journal.php`
- `users.php`
- `user-create.php`
- `user-edit.php`
- `user-reset-password.php`
- `settings.php`

Working admin CRUD now:

- students
- staff
- users
- SQL console for direct database maintenance

Starter/admin-shell-only modules:

- expenses
- accounts
- journal

Implemented finance workflows:

- payments statement import and draft review
- payouts suggestion/history

---

## `teacher/`

Teacher route/controller entry files.

Current files:

- `dashboard.php`
- `students.php`
- `add-student.php`
- `student-edit.php`
- `payouts.php`
- `profile.php`

Working teacher module now:

- own students list
- create own student
- edit own student
- payouts visibility and current-month payout calculation
- profile with password change

---

## `views/`

Presentation layer.

These are templates, not route entry files.

### `views/layouts/`

Page shells:

- `guest.php`
- `admin.php`
- `teacher.php`

Purpose:

- define `<html>`, `<head>`, shared layout wrappers, CSS include

### `views/auth/`

Authentication-facing templates.

Current:

- `login.php`

### `views/admin/`

Admin page templates.

Current:

- `dashboard.php`
- `payouts.php`
- `settings.php`
- `students.php`
- `student_form.php`
- `staff.php`
- `staff_form.php`
- `users.php`
- `user_form.php`
- `user_reset_password.php`
- `placeholder.php`

### `views/teacher/`

Teacher page templates.

Current:

- `dashboard.php`
- `students.php`
- `student_form.php`
- `payouts.php`
- `profile.php`
- `placeholder.php`

### `views/partials/`

Shared fragments reused by layouts/forms.

Current:

- `admin_header.php`
- `admin_sidebar.php`
- `teacher_header.php`
- `teacher_sidebar.php`
- `flash.php`
- `student_form_fields.php`
- `staff_form_fields.php`
- `user_form_fields.php`

Purpose:

- shared navigation
- shared flash rendering
- shared form field blocks

---

## `assets/`

Static front-end assets.

Current:

- `assets/css/app.css`

Purpose:

- shared app styling
- dark theme
- admin and teacher shell styling
- CRUD list/form/table styling

---

## `database/`

Database definition.

Current:

- `database/schema.sql`

Purpose:

- PostgreSQL enums
- table definitions
- indexes
- permanent base seed data only

This is the executable schema.

---

## `scripts/`

Manual utility scripts.

Current:

- `scripts/setup.php`

Purpose:

- optional manual provisioning

The app does not require this in normal local usage because provisioning is automatic at bootstrap.

---

## `uploads/`

Runtime file storage.

Current:

- `uploads/meta/ico.jpg`
- `uploads/profiles/.gitkeep`

Purpose:

- login branding image
- profile uploads folder placeholder

---

## 9. Database Model

## Core tables

```text
users
staff
students
plans
expense_categories
```

## Supporting tables

```text
none
```

## Business tables

```text
student_charges
payments
statement_import_batches
statement_import_rows
expenses
staff_payouts
```

## Accounting tables

```text
accounts
journal_entries
journal_entry_lines
```

---

## 10. Important Tables

### `users`

System login accounts.

Important fields:

```text
staff_id required
username unique
password_hash
role enum[admin, teacher]
is_active
last_login_at
created_at
updated_at
```

Rules:

- passwords are hashed only
- every user must have a linked `staff_id`
- teacher users still depend on that link for route access

### `staff`

Represents paid people in the business.

Examples:

- teacher
- manager
- accountant
- assistant

Important fields:

```text
role
first_name
family_name
father_name
status enum[active, archived]
payout_card_number
phone
email
comments
```

### `students`

Operational student records.

Important fields:

```text
first_name
family_name
father_name
phone
email
status enum[active, paused, archived]
plan_id
staff_id
discount_amount
joined_at
comments
```

Meaning:

- `plan_id` links to the assigned commercial plan
- `staff_id` links to the responsible teacher/staff member
- teacher pages filter by this `staff_id`

### `plans`

Pricing / lesson plan definitions used during student assignment.

Important fields:

```text
name
lesson_count
lesson_price
teacher_share_per_lesson
is_assignable
```

Fresh reset behavior:

- table exists
- starts with zero rows
- plans can be managed from the admin plans page after bootstrap

### `accounts`

Flat chart of accounts.

Important fields:

```text
code unique
name
type enum[asset, liability, equity, revenue, expense]
is_active
```

This chart is intentionally flat.
Totals are expected to group by `type`, not parent-child hierarchy.

Starter seeded accounts:

```text
1010 Cash
1020 Bank
1100 Accounts Receivable - Students
2100 Accounts Payable - Staff
3100 Owner Equity
4100 Revenue - Student Fees
5100 Staff Payout Expense
5200 Advertising Expense
5300 Software Expense
5400 Other Expense
```

### `journal_entries` and `journal_entry_lines`

Accounting event header and detail rows.

Purpose:

- one journal entry = one accounting event
- journal lines = debit/credit details

Rules:

- entry must be balanced
- business source is tracked by `source_type` + `source_id`
- reversal is additive, not destructive

---

## 11. CRUD Structure

The first CRUD slice follows one repeated pattern.

For a module like students:

1. route file handles auth + GET/POST
2. repository loads/saves DB rows
3. form service validates and normalizes input
4. view renders list or form
5. layout wraps the view

Example student files:

```text
admin/students.php
admin/student-create.php
admin/student-edit.php
app/Repositories/StudentRepository.php
app/Services/StudentFormService.php
views/admin/students.php
views/admin/student_form.php
views/partials/student_form_fields.php
```

Example staff files:

```text
admin/staff.php
admin/staff-create.php
admin/staff-edit.php
app/Repositories/StaffRepository.php
app/Services/StaffFormService.php
views/admin/staff.php
views/admin/staff_form.php
views/partials/staff_form_fields.php
```

Example user files:

```text
admin/users.php
admin/user-create.php
admin/user-edit.php
admin/user-reset-password.php
app/Repositories/UserRepository.php
app/Services/UserFormService.php
views/admin/users.php
views/admin/user_form.php
views/admin/user_reset_password.php
views/partials/user_form_fields.php
```

Teacher students reuse the same repository/service layer but apply ownership constraints.

---

## 12. Current CRUD Rules

### Students

Admin can:

- list all students
- search by text
- filter by status
- create
- edit
- reassign teacher
- archive/unarchive by status

Teacher can:

- list only own students
- search only within own students
- filter by status
- create student automatically assigned to self
- edit only own students
- archive/unarchive only own students

Excluded from this first pass:

- profile photos
- charge generation
- accounting posting

### Staff

Admin can:

- list all staff
- search
- filter by status
- create
- edit
- archive/unarchive by status

Excluded from this first pass:

- profile photos
- linked user-account management
- payout workflow automation

### Users

Admin can:

- list all users
- search by username or linked staff
- filter by role
- filter by active/inactive access
- filter by linked staff
- create with the configured starter password
- edit core identity fields
- reset password through a dedicated reset action
- deactivate/reactivate by `is_active`

Users rules:

- every user must be linked to a real staff record
- passwords are always stored as hashes
- the main edit form does not change passwords
- reset sets the password back to the configured starter value

### Delete policy

There is no hard delete in the current CRUD UI.

Archive behavior is status-based:

- staff -> `active` / `archived`
- students -> `active` / `paused` / `archived`

---

## 13. Setup and Runtime

The app auto-provisions the DB on first request.

Provisioning behavior:

1. connect to PostgreSQL from `config.php`
2. create database if missing
3. apply `database/schema.sql`
4. seed permanent reference data from schema:
   - accounts
   - expense categories
5. seed required bootstrap identity data in `DatabaseProvisioner`:
   - admin staff
   - teacher staff
   - admin user
   - teacher user
6. create upload directories if needed

After a fresh reset, the intended base state is:

- `plans` is empty
- `students` is empty
- `statement_import_batches` is empty
- `statement_import_rows` is empty
- login works with the two starter accounts
- operational data is added manually later

### Manual setup

Optional only:

```bash
php scripts/setup.php
```

Laragon example:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe scripts\setup.php
```

### Runtime requirements

- PHP 8.1+
- `PDO`
- `pdo_pgsql`
- `pgsql`
- PostgreSQL reachable from PHP
- write access to `uploads/profiles`

### Timezone behavior

Configured app timezone:

```text
Europe/Kyiv
```

Current intended behavior:

- PHP timezone is set to `Europe/Kyiv`
- PostgreSQL session timezone is set to `Europe/Kyiv`
- audit/date-time fields use timezone-aware timestamps where implemented

---

## 14. Configuration

Main config file:

```text
config.php
```

Contains:

- `app.name`
- `app.base_path`
- `app.timezone`
- `auth.default_password`
- database host / port / name / user / password / charset

For the current Laragon layout:

```text
/MICS
```

If `app.base_path` is wrong, URLs, assets, redirects, and images will break.

---

## 15. Security and Application Rules

Current enforced rules:

- passwords are hashed
- protected pages gate access before rendering
- teacher routes require linked `staff_id`
- CSRF tokens are checked on forms
- business data and accounting data stay structurally separated
- accounting service uses transactions for posting logic

Operational rules currently reflected in code and schema:

- use foreign keys
- use status enums / status fields
- keep financial posting explicit
- avoid hard delete in normal workflow
- keep bootstrap data minimal and durable

---

## 16. What Is Next

The current most natural next build order is:

1. deepen payment posting and reconciliation workflow
2. implement expenses workflow
3. deepen staff payouts workflow
4. connect business documents to accounting posting
5. expose journal and account views as real finance tools
6. add more profile/media management if needed
7. decide whether the SQL console should remain raw or become more guided

---

## 17. Quick Orientation for a New Developer

If you are opening this project for the first time:

1. read `config.php`
2. read `app/bootstrap.php`
3. read `app/helpers.php`
4. open `admin/students.php` and `teacher/students.php`
5. inspect `StudentRepository` and `StudentFormService`
6. inspect `views/admin/student_form.php`
7. read `database/schema.sql`

That path shows the real architecture faster than reading isolated files at random.

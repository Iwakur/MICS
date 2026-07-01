# MICS Session Handoff

## Resume Objective

Continue the first vertical slice: minimal Laravel username/password authentication with `admin` and `teacher` dashboard access. The owner is learning Laravel and should write the application code with guided, reviewed steps. Explain concepts and alternatives before implementing code automatically.

## Decisions Made

- Students are records, not authenticated users.
- Every administrator is also a teacher, but not every teacher is an administrator.
- `users.role` determines dashboard access (`admin` or `teacher`).
- Inactive accounts remain stored but must not authenticate.
- Keep Laravel's `email_verified_at` for future compatibility.
- Do not add `last_login_at` yet; it is not required for minimal Laravel authentication.
- Do not add `staff_id` until the staff table is designed.
- Keep one CSS entrypoint at `resources/css/app.css` and use Tailwind utilities initially.

## Work Completed

The default users migration was adapted to include:

- unique `username`
- unique `email`
- Laravel `password` and `email_verified_at`
- string `role`, defaulting to `teacher`
- boolean `is_active`, defaulting to `true`
- Laravel remember token and timestamps

A backed `UserRole` enum was drafted with `Admin` and `Teacher` cases. The `User` model was partially updated with role and active casts/defaults.

Some commands may already have been run, but their results were not verified. Rerun the relevant checks after correcting the files.

## Current Problems to Fix First

1. `app/UserRole.php` declares namespace `App\Enums`. Its path must therefore be `app/Enums/UserRole.php` for PSR-4 autoloading.
2. `app/Models/User.php` needs `use App\Enums\UserRole;`.
3. The model's fillable list still contains `name`, but the migration replaced `name` with `username`. Replace `name` with `username`.
4. `database/factories/UserFactory.php` still generates `name` and does not generate `username`, `role`, or `is_active`.
5. `PLAN.md` contains an older Step 1 review that mentions removing `email_verified_at` and adding `last_login_at`. Reconcile it with the newer decisions above.

Do not begin controllers or login views until these inconsistencies are fixed and reviewed.

## First Actions Next Session

1. Start DDEV:

   ```bash
   ddev start
   ```

2. Fix the enum path/import and the `User` fillable list.
3. Format only the changed PHP files:

   ```bash
   ddev exec ./vendor/bin/pint app/Enums/UserRole.php app/Models/User.php
   ```

4. Rebuild the disposable local database and inspect the table:

   ```bash
   ddev artisan migrate:fresh
   ddev artisan db:table users
   ```

5. Ask the agent to review the enum, model, migration, and command results before proceeding.

## Next Guided Step After Review

Update `database/factories/UserFactory.php` so its default user matches the schema. Add readable factory states for admin, teacher, and inactive users. Then write focused model/factory tests before building routes or the login form.

## Definition of Done for the Current Model Step

- `UserRole` autoloads from the correct path.
- `User` imports and casts `UserRole` correctly.
- Fillable fields match real database columns.
- Password assignment uses Laravel's `hashed` cast.
- `role` returns a `UserRole` value.
- `is_active` returns a boolean.
- Migration succeeds on PostgreSQL.
- Pint reports no formatting issues.

## Reference Documents

- `PLAN.md` — roadmap, acceptance criteria, and guided steps
- `README.md` — product direction and legacy context
- `AGENTS.md` — repository conventions and learning preference
- `legacy(self-created)/` — design evidence only, not implementation truth

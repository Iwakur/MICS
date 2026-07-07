# Seeding Decisions

## Implemented source boundary

The seed uses the latest worksheet, `Травень26`, from the local `Customers DB.ods` file. The ODS file and `BDB.txt` are ignored by Git because they contain private operational and payment data.

The source files are analysis inputs only. Runtime seeding uses reviewed PHP arrays and never reads local documents.

## Implemented seed

- One administrator account linked to a teaching staff profile.
- One teacher account linked to Максим Гузьо Степанович.
- Twelve pupils assigned to that teacher whose billing mode, rate, and discount are explicit in `Травень26`.
- Two per-lesson rates: 300 ₴ with a 150 ₴ teacher share, and 350 ₴ with a 175 ₴ teacher share.
- Eight plans with decimal lesson counts and exact source names and amounts.
- Ukrainian expense categories derived from the source service/purpose list.
- No payments, payment confirmations, bank snapshots, expenses, or historical debt.
- No payout card numbers from `BDB.txt`.

## Confirmed corrections

1. Ordinary 300 ₴ and 350 ₴ lessons use 60 minutes because the schema requires a duration and the source does not provide one.
2. Joined dates and plan start dates use `2026-04-01`.
3. Source markers such as `D1` and `D2` are not preserved in names; the teacher keeps the real full name.
4. `4.5` is stored in the decimal `lesson_count` field on the relevant plans.
5. The fresh seed imports one teacher group only, not the entire source workbook.
6. Rows with no rate or plan remain ignored.
7. English remains the default locale for both seeded accounts.

## Seed credentials

These credentials are created only in `local` and `testing` environments:

- Administrator: `admin` / `password`
- Teacher: `teacher` / `password`

They must never be used as production credentials.

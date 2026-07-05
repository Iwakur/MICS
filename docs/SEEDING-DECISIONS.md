# School Data and Seeding Decisions

Edit this file directly. Replace each `TODO` answer and select choices by changing `[ ]` to `[x]`. Codex will read the completed file before implementing the school seed, imports, or bank-account changes.

Do not add student names, bank account numbers, passwords, or other private values here. The source customer CSV stays local and is ignored by Git.

## Confirmed Decisions

- A package price is the complete monthly charge. It is not calculated from the average lesson count.
- Values such as `4.5` are descriptive average-lesson metadata only.
- Package suffixes such as `-350`, `-300`, `-45`, `-30`, and `-325` identify the per-lesson price/category represented by that package. They are metadata and do not calculate the monthly charge.
- Financial calculations use the stored monthly package price and stored monthly teacher share.
- The first production administrator is created once and can then create the remaining school records and accounts.

## 1. Package Names and Visibility

Should the package names be stored exactly as written in the spreadsheet?

- [x] Yes, preserve the Ukrainian names and suffixes.
- [ ] No, rename them using the mapping below.

Answer or mapping: TODO

Should all 12 spreadsheet packages initially be available for assignment to students?

- [ ] Yes, make all 12 assignable.
- [ ] No, seed all 12 but archive the packages listed below.
- [ ] Seed only the packages listed below.

Answer/list: TODO

## 2. Package Metadata

Recommended model: keep `plan_price` and `teacher_monthly_amount` as financial values; add nullable descriptive fields for average lessons and advertised per-lesson price. Neither metadata field participates in closing or salary calculations.

- [ ] Use the recommended model.
- [ ] Keep this metadata only in the plan note.
- [ ] Do not store average lessons or advertised per-lesson price.

Answer: TODO

What does an average such as `4.5` mean in reporting language (for example, average lessons per month)?

Answer: TODO

Are package lessons always the same duration?

- [ ] Yes. Duration in minutes: TODO
- [ ] No. Duration varies and should be optional metadata.
- [ ] Duration is not relevant for packages.

Answer: TODO

## 3. Package Effective Date

From which month should the spreadsheet prices be effective? Use `YYYY-MM`.

Answer: TODO

When prices change later:

- [x] Add a future-effective rate and preserve old months.
- [ ] Replace the current rate everywhere.

## 4. Currency

- [ ] The entire school uses Ukrainian hryvnia (`UAH`, displayed as `₴`).
- [ ] Multiple currencies are required.
- [ ] Another single currency is used: TODO

Answer: TODO

If multiple currencies are required, explain which records can use which currency and whether conversion is needed:

Answer: TODO

## 5. Initial Administrator

Recommended production behavior is username `admin` with a secure password entered interactively. The application must never commit or reseed that password.

- [ ] Use interactive `app:bootstrap-administrator` with username `admin`.
- [ ] Create it non-interactively from protected VPS environment variables.
- [ ] Development may use `admin` / `admin`, but production must require a secure password.

Answer: TODO

Should the first administrator's staff record be teaching-capable?

- [ ] No, administrator only.
- [ ] Yes, administrator and teacher.

Answer: TODO

## 6. Staff Seed and Import

Which spreadsheet staff roles should become reusable staff roles?

Answer/list: TODO

Should actual staff be imported now?

- [ ] No. Create staff manually after login.
- [ ] Import staff as inactive records requiring review.
- [ ] Import only the named staff listed below.

Answer/list: TODO

Should salary values from the spreadsheet be imported?

- [ ] No, configure salaries manually.
- [ ] Yes, as current fixed salaries after preview and confirmation.
- [ ] Yes, with another rule explained below.

Answer: TODO

## 7. Student Import

Recommended first deployment: do not seed students. Build a separate dry-run importer later so outdated records cannot silently become active.

- [ ] Do not import students for the first deployment.
- [ ] Import every recognized student as inactive/pending review.
- [ ] Import only students selected in a cleaned file.

Answer: TODO

If importing later, how should a duplicate be recognized?

- [ ] Exact normalized full name.
- [ ] Phone number.
- [ ] Email address.
- [ ] A new external/source identifier.
- [ ] Combination explained below.

Answer: TODO

## 8. Expenses and Services

Which stable expense categories should be seeded? Suggested categories from the spreadsheet are Advertising, Taxes, CRM/software, Learning platforms, Staff salaries, Trainer salaries, and Other services.

Answer/list: TODO

Should named services such as Facebook, CRM, and Canvas be reusable vendors/services, or remain free-text expense notes?

- [ ] Keep them as free-text notes for now.
- [ ] Add reusable vendors/services.

Answer: TODO

## 9. Bank and Cash Accounts

How many real balances must be reconciled separately?

- [ ] One combined bank balance.
- [ ] Multiple bank accounts.
- [ ] Multiple bank accounts plus a cash account.

Answer and account labels (never include full account numbers): TODO

For the first deployment:

- [ ] Enter only each account's actual monthly closing balance manually.
- [ ] Import bank statement CSV files and match individual transactions.
- [ ] Start manually and add statement import after deployment.

Answer: TODO

Which bank exports are available (`CSV`, `XLSX`, another format), and from which bank/provider?

Answer: TODO

Should transfers between the school's own accounts be tracked and excluded from income/expense?

- [ ] Yes.
- [ ] No.
- [ ] Not applicable because there is only one account.

Answer: TODO

## 10. Deployment Approval

After the decisions above are implemented and tested:

- [ ] Deploy with reference roles, categories, packages, and the first administrator only.
- [ ] Also include reviewed staff.
- [ ] Also include reviewed students.
- [ ] Also include opening bank/cash balances.

Desired production opening date/month: TODO

Additional rules or concerns: TODO

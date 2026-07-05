# MICS Product Workflows

## Initial Setup

1. Create active staff roles. Teaching roles must enable `can_teach`.
2. Create staff records and select fixed or dynamic compensation.
3. Create login accounts linked to staff. Admin accounts may link to any active staff; teacher accounts require teaching-capable staff.
4. Create lesson types, plans, and expense categories.
5. Create students with a teacher, billing type, start date, and optional discount.

Catalog, assignment, status, billing, and discount edits become effective in the next open month after the latest closed month. Earlier months continue using their historical configuration.

## Monthly Cycle

1. Teachers enter lesson counts for their assigned per-lesson students while the month is open.
2. Administrators record payment evidence as drafts and validate only after checking bank/cash evidence.
3. Administrators preview and close the selected month. Closing generates student charges, salary drafts, and next-month opening debt.
4. Administrators review/validate charges, salaries, and manual expenses. Validated records are immutable.
5. Corrections use an attributed charge adjustment or linked partial/full refund; cumulative refunds cannot exceed the original payment.
6. Administrators reconcile expected bank close against the actual statement balance and explain any variance.

Expected bank close is opening bank balance plus validated receipts dated in the month minus validated expenses assigned to the month. The reconciled actual close becomes the next reconciled month’s opening balance.

## Reopen and Correction

Reopening a billing month requires a meaningful reason. Reclosing refreshes only draft calculations; validated charges and salaries remain unchanged. Reopening bank reconciliation also requires a reason and retains its audit events.

If a payment was too large, record one or more refunds from the validated original. If it belonged to another student, refund the original fully and create/validate a replacement payment for the correct student. Never edit or delete a validated payment.

## Three-Month Example

- July: two lessons at 100 create a 200 charge; a validated 150 payment leaves 50 debt.
- August: a rate of 120 and discount of 20 take effect, producing a 100 charge. A 50 refund of July’s payment raises carried July debt from 50 to 100.
- September: closing carries August debt. If September is reopened, validated September records remain fixed while drafts may regenerate.

This scenario is executable as `ddev composer test:workflow` and is the reference regression for month-to-month consistency.

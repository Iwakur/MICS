<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1><?= e($pageTitle) ?></h1>
        <p><?= e($pageDescription) ?></p>
        <p><?= e($pageNote) ?></p>
    </div>
</section>

<section class="panel">
    <h2>Import Statement</h2>
    <form method="post" enctype="multipart/form-data" class="form-grid">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="upload_statement">
        <div class="field">
            <span>Bank CSV</span>
            <input type="file" name="statement_file" accept=".csv,text/csv" required>
        </div>
        <div class="field field-submit">
            <span>Workflow</span>
            <button type="submit" class="button button-primary">Upload And Parse</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <h2>Import Batches</h2>
            <p class="table-subtext">Each batch keeps the uploaded file history plus row review progress.</p>
        </div>
    </div>
    <?php if ($batches === []) { ?>
        <p>No statements have been imported yet.</p>
    <?php } else { ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Batch</th>
                    <th>Account</th>
                    <th>Rows</th>
                    <th>Queue</th>
                    <th>Finished</th>
                    <th>Imported</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($batches as $batch) { ?>
                    <?php
                    $batchLink = app_url('admin/imports.php?batch_id='.(int) $batch['id']);
                    $queueCount = (int) $batch['rows_new'] + (int) $batch['rows_unmatched'];
                    $finishedCount = (int) $batch['rows_draft_created'] + (int) $batch['rows_ignored'] + (int) $batch['rows_deleted'];
                    ?>
                    <tr>
                        <td>
                            <strong><a href="<?= e($batchLink) ?>"><?= e($batch['original_filename']) ?></a></strong><br>
                            <span class="table-subtext">Batch #<?= e((string) $batch['id']) ?></span>
                        </td>
                        <td>
                            <?= e((string) ($batch['account_number'] ?: 'Unknown account')) ?><br>
                            <span class="table-subtext"><?= e((string) ($batch['currency'] ?: '')) ?></span>
                        </td>
                        <td>
                            <strong><?= e((string) $batch['total_rows']) ?></strong><br>
                            <span class="table-subtext">New rows: <?= e((string) $batch['new_rows']) ?> | Duplicates: <?= e((string) $batch['duplicate_rows']) ?></span>
                        </td>
                        <td>
                            <span class="status-pill status-new">New <?= e((string) $batch['rows_new']) ?></span>
                            <span class="status-pill status-unmatched">Unmatched <?= e((string) $batch['rows_unmatched']) ?></span><br>
                            <span class="table-subtext">Open queue: <?= e((string) $queueCount) ?></span>
                        </td>
                        <td>
                            <span class="status-pill status-draft_created">Drafts <?= e((string) $batch['rows_draft_created']) ?></span>
                            <span class="status-pill status-ignored">Ignored <?= e((string) $batch['rows_ignored']) ?></span>
                            <span class="status-pill status-deleted">Deleted <?= e((string) $batch['rows_deleted']) ?></span><br>
                            <span class="table-subtext">Resolved: <?= e((string) $finishedCount) ?></span>
                        </td>
                        <td><?= e(date('Y-m-d H:i', strtotime((string) $batch['created_at']))) ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <h2>Review Queue</h2>
            <p class="table-subtext">Default view shows unresolved rows only. Filter by batch or status to inspect history.</p>
        </div>
    </div>
    <form method="get" class="filter-bar">
        <select name="batch_id">
            <option value="">All batches</option>
            <?php foreach ($batches as $batch) { ?>
                <option value="<?= e((string) $batch['id']) ?>"<?= $batchId === (int) $batch['id'] ? ' selected' : '' ?>>
                    <?= e('#'.(string) $batch['id'].' - '.$batch['original_filename']) ?>
                </option>
            <?php } ?>
        </select>
        <select name="status">
            <option value="">Open queue only</option>
            <?php foreach ($statuses as $rowStatus) { ?>
                <option value="<?= e($rowStatus) ?>"<?= $status === $rowStatus ? ' selected' : '' ?>>
                    <?= e($statusLabel($rowStatus)) ?>
                </option>
            <?php } ?>
        </select>
        <button type="submit" class="button button-ghost">Apply</button>
        <a class="button button-ghost" href="<?= e(app_url('admin/imports.php')) ?>">Reset</a>
    </form>
</section>

<section class="panel">
    <?php if ($rows === []) { ?>
        <p>No imported rows match the current filters.</p>
    <?php } else { ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Row</th>
                    <th>Bank Details</th>
                    <th>Detected Match</th>
                    <th>Draft Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row) { ?>
                    <?php
                    $defaultMonth = date('Y-m', strtotime((string) $row['operation_date']));
                    $canCreate = in_array((string) $row['status'], ['new', 'unmatched'], true)
                        && $row['payment_id'] === null
                        && $row['expense_id'] === null
                        && $row['payout_id'] === null;
                    ?>
                    <tr>
                        <td>
                            <strong><?= e(date('Y-m-d', strtotime((string) $row['operation_date']))) ?></strong><br>
                            <span class="status-pill status-<?= e((string) $row['direction']) ?>"><?= e(ucfirst((string) $row['direction'])) ?></span>
                            <span class="status-pill status-<?= e((string) $row['status']) ?>"><?= e($statusLabel((string) $row['status'])) ?></span><br>
                            <span class="table-subtext">Amount: <?= e(number_format((float) $row['amount'], 2)) ?> <?= e((string) $row['currency']) ?></span><br>
                            <span class="table-subtext">Doc: <?= e((string) ($row['document_number'] ?: 'No number')) ?></span><br>
                            <span class="table-subtext">Batch #<?= e((string) $row['batch_id']) ?> / row <?= e((string) $row['row_number']) ?></span>
                        </td>
                        <td>
                            <strong><?= e((string) ($row['correspondent_name'] ?: 'Unknown correspondent')) ?></strong><br>
                            <span class="table-subtext"><?= e((string) ($row['bank_name'] ?: 'Unknown bank')) ?><?= $row['bank_mfo'] ? ' | MFO '.e((string) $row['bank_mfo']) : '' ?></span><br>
                            <span class="table-subtext"><?= e((string) ($row['correspondent_account'] ?: 'No correspondent account')) ?></span><br>
                            <span class="table-subtext"><?= e((string) ($row['payment_purpose'] ?: 'No payment purpose')) ?></span>
                        </td>
                        <td>
                            <?php if ($row['created_document_label'] !== null) { ?>
                                <strong><?= e((string) $row['created_document_label']) ?></strong><br>
                                <span class="table-subtext">This row is already linked to a finance draft.</span>
                            <?php } elseif ($row['matched_student_name'] !== null) { ?>
                                <strong><?= e((string) $row['matched_student_name']) ?></strong><br>
                                <span class="table-subtext">Suggested student from row text.</span>
                            <?php } elseif ($row['suggested_students'] !== []) { ?>
                                <?php foreach ($row['suggested_students'] as $suggestedStudent) { ?>
                                    <strong><?= e($suggestedStudent['label']) ?></strong><br>
                                    <span class="table-subtext"><?= e($suggestedStudent['meta']) ?> | <?= e(ucfirst($suggestedStudent['status'])) ?></span><br>
                                <?php } ?>
                            <?php } else { ?>
                                <span class="status-pill status-unmatched">No automatic suggestion</span>
                            <?php } ?>
                        </td>
                        <td>
                            <?php if ($canCreate && $row['direction'] === 'incoming') { ?>
                                <form method="post" class="stack review-card">
                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="create_payment_draft">
                                    <input type="hidden" name="row_id" value="<?= e((string) $row['id']) ?>">
                                    <input type="hidden" name="batch_filter" value="<?= e($batchId !== null ? (string) $batchId : '') ?>">
                                    <input type="hidden" name="status_filter" value="<?= e($status) ?>">
                                    <div class="field">
                                        <span>Student</span>
                                        <select name="student_id" required>
                                            <option value="">Choose student</option>
                                            <?php foreach ($studentsById as $studentOption) { ?>
                                                <option value="<?= e((string) $studentOption['id']) ?>"<?= $row['default_student_id'] === $studentOption['id'] ? ' selected' : '' ?>>
                                                    <?= e($studentOption['label'].' | '.$studentOption['meta']) ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <span>Covered month</span>
                                        <input type="month" name="covered_month" value="<?= e($defaultMonth) ?>" required>
                                    </div>
                                    <button type="submit" class="button button-primary">Create Payment Draft</button>
                                </form>
                            <?php } elseif ($canCreate && $row['direction'] === 'outgoing') { ?>
                                <form method="post" class="stack review-card">
                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="create_expense_draft">
                                    <input type="hidden" name="row_id" value="<?= e((string) $row['id']) ?>">
                                    <input type="hidden" name="batch_filter" value="<?= e($batchId !== null ? (string) $batchId : '') ?>">
                                    <input type="hidden" name="status_filter" value="<?= e($status) ?>">
                                    <div class="field">
                                        <span>Expense category</span>
                                        <select name="category_id" required>
                                            <option value="">Choose category</option>
                                            <?php foreach ($expenseCategories as $category) { ?>
                                                <option value="<?= e((string) $category['id']) ?>"><?= e($category['name'].' ('.$category['code'].')') ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <span>Paid from account</span>
                                        <select name="paid_from_account_id" required>
                                            <option value="">Choose account</option>
                                            <?php foreach ($paidFromAccounts as $account) { ?>
                                                <option value="<?= e((string) $account['id']) ?>"><?= e($account['code'].' - '.$account['name']) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="button button-primary">Create Expense Draft</button>
                                </form>

                                <form method="post" class="stack review-card import-secondary-form">
                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="create_payout_draft">
                                    <input type="hidden" name="row_id" value="<?= e((string) $row['id']) ?>">
                                    <input type="hidden" name="batch_filter" value="<?= e($batchId !== null ? (string) $batchId : '') ?>">
                                    <input type="hidden" name="status_filter" value="<?= e($status) ?>">
                                    <div class="field">
                                        <span>Staff</span>
                                        <select name="staff_id" required>
                                            <option value="">Choose staff</option>
                                            <?php foreach ($staffById as $staffOption) { ?>
                                                <option value="<?= e((string) $staffOption['id']) ?>"><?= e($staffOption['label'].' | '.$staffOption['meta']) ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <button type="submit" class="button button-ghost">Create Payout Draft</button>
                                </form>
                            <?php } else { ?>
                                <p class="table-subtext">No draft action available for this row right now.</p>
                            <?php } ?>

                            <div class="row-actions review-actions">
                                <?php if ((string) $row['status'] === 'ignored' || (string) $row['status'] === 'deleted') { ?>
                                    <form method="post">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="restore_row">
                                        <input type="hidden" name="row_id" value="<?= e((string) $row['id']) ?>">
                                        <input type="hidden" name="batch_filter" value="<?= e($batchId !== null ? (string) $batchId : '') ?>">
                                        <input type="hidden" name="status_filter" value="<?= e($status) ?>">
                                        <button type="submit" class="button button-ghost">Restore To Queue</button>
                                    </form>
                                <?php } else { ?>
                                    <form method="post">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="ignore_row">
                                        <input type="hidden" name="row_id" value="<?= e((string) $row['id']) ?>">
                                        <input type="hidden" name="batch_filter" value="<?= e($batchId !== null ? (string) $batchId : '') ?>">
                                        <input type="hidden" name="status_filter" value="<?= e($status) ?>">
                                        <button type="submit" class="button button-ghost">Ignore</button>
                                    </form>
                                    <form method="post">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="delete_row">
                                        <input type="hidden" name="row_id" value="<?= e((string) $row['id']) ?>">
                                        <input type="hidden" name="batch_filter" value="<?= e($batchId !== null ? (string) $batchId : '') ?>">
                                        <input type="hidden" name="status_filter" value="<?= e($status) ?>">
                                        <button type="submit" class="button button-ghost">Delete From Queue</button>
                                    </form>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</section>

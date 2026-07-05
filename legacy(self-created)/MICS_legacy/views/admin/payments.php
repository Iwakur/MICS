<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1>Payments</h1>
        <p>Actual payment records, whether created manually or from imported bank rows.</p>
    </div>
    <a class="button button-primary" href="<?= e(app_url('admin/payment-create.php')) ?>">Create Payment</a>
</section>

<section class="panel">
    <form method="get" class="filter-bar">
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $paymentStatus) { ?>
                <option value="<?= e($paymentStatus) ?>"<?= $status === $paymentStatus ? ' selected' : '' ?>><?= e(ucfirst($paymentStatus)) ?></option>
            <?php } ?>
        </select>
        <select name="student_id">
            <option value="">All students</option>
            <?php foreach ($students as $student) { ?>
                <?php $studentName = person_name_from_row($student); ?>
                <option value="<?= e((string) $student['id']) ?>"<?= $studentId === (string) $student['id'] ? ' selected' : '' ?>>
                    <?= e($studentName !== '' ? $studentName : ('Student #'.$student['id'])) ?>
                </option>
            <?php } ?>
        </select>
        <button type="submit" class="button button-ghost">Apply</button>
        <a class="button button-ghost" href="<?= e(app_url('admin/payments.php')) ?>">Reset</a>
    </form>
</section>

<section class="panel">
    <?php if ($payments === []) { ?>
        <p>No payment records match the current filters.</p>
    <?php } else { ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Student</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Method / Source</th>
                    <th>Status</th>
                    <th>Covered Month</th>
                    <th>Origin</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($payments as $payment) { ?>
                    <?php $studentName = person_name_from_row($payment); ?>
                    <tr>
                        <td>
                            <strong><?= e($studentName) ?></strong><br>
                            <span class="table-subtext"><?= e((string) ($payment['external_reference'] ?: 'No external reference')) ?></span>
                        </td>
                        <td><?= e(date('Y-m-d H:i', strtotime((string) $payment['payment_date']))) ?></td>
                        <td><?= e(number_format((float) $payment['amount'], 2)) ?></td>
                        <td>
                            <strong><?= e($payment['method']) ?></strong><br>
                            <span class="table-subtext"><?= e($payment['source']) ?></span>
                        </td>
                        <td><span class="status-pill status-<?= e($payment['status']) ?>"><?= e(ucfirst($payment['status'])) ?></span></td>
                        <td><?= e($payment['covered_month'] !== null ? date('Y-m', strtotime((string) $payment['covered_month'])) : 'Not set') ?></td>
                        <td><?= e($payment['import_row_id'] !== null ? ('Import row #'.$payment['import_row_id']) : 'Manual') ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="button button-ghost" href="<?= e(app_url('admin/payment-edit.php?id='.(int) $payment['id'])) ?>">Edit</a>
                                <?php if ($payment['status'] === 'pending') { ?>
                                    <form method="post">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="confirm_payment">
                                        <input type="hidden" name="payment_id" value="<?= e((string) $payment['id']) ?>">
                                        <button type="submit" class="button button-ghost">Confirm</button>
                                    </form>
                                <?php } ?>
                                <?php if ($payment['status'] !== 'void') { ?>
                                    <form method="post">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="void_payment">
                                        <input type="hidden" name="payment_id" value="<?= e((string) $payment['id']) ?>">
                                        <button type="submit" class="button button-ghost">Void</button>
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

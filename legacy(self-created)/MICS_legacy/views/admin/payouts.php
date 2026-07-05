<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1>Staff Payouts</h1>
        <p>Calculated suggestion drafts plus saved payout documents, including drafts created from imported outgoing rows.</p>
    </div>
</section>

<section class="panel">
    <h2>Current-Month Suggestions</h2>
    <p>Suggestions for <?= e($periodLabel) ?> are calculated from active students and each assigned plan's teacher share.</p>

    <div class="table-wrap">
        <table class="data-table">
            <thead>
            <tr>
                <th>Staff</th>
                <th>Role</th>
                <th>Active Students</th>
                <th>Suggested Payout</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($suggestions as $suggestion) { ?>
                <?php $staffName = person_name_from_row($suggestion); ?>
                <tr>
                    <td>
                        <strong><?= e($staffName) ?></strong><br>
                        <span class="table-subtext"><?= e((string) ($suggestion['fixed_salary_amount'] !== null ? 'Fixed salary' : 'Teacher-share based')) ?></span>
                    </td>
                    <td><?= e($suggestion['role']) ?></td>
                    <td><?= e((string) $suggestion['student_count']) ?></td>
                    <td><?= e(number_format((float) $suggestion['suggested_amount'], 2)) ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="create_draft">
                            <input type="hidden" name="staff_id" value="<?= e((string) $suggestion['id']) ?>">
                            <button type="submit" class="button button-ghost">Create Draft</button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <form method="get" class="filter-bar">
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $payoutStatus) { ?>
                <option value="<?= e($payoutStatus) ?>"<?= $status === $payoutStatus ? ' selected' : '' ?>><?= e(ucfirst($payoutStatus)) ?></option>
            <?php } ?>
        </select>
        <select name="staff_id">
            <option value="">All staff</option>
            <?php foreach ($staff as $staffMember) { ?>
                <?php $staffName = person_name_from_row($staffMember); ?>
                <option value="<?= e((string) $staffMember['id']) ?>"<?= $staffId === (string) $staffMember['id'] ? ' selected' : '' ?>>
                    <?= e($staffName) ?>
                </option>
            <?php } ?>
        </select>
        <button type="submit" class="button button-ghost">Apply</button>
        <a class="button button-ghost" href="<?= e(app_url('admin/payouts.php')) ?>">Reset</a>
    </form>
</section>

<section class="panel">
    <h2>Saved Payout Records</h2>

    <?php if ($history === []) { ?>
        <p>No payout records match the current filters.</p>
    <?php } else { ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Staff</th>
                    <th>Payout Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Posted</th>
                    <th>Origin</th>
                    <th>Comment</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($history as $payout) { ?>
                    <?php $staffName = person_name_from_row($payout); ?>
                    <tr>
                        <td>
                            <strong><?= e($staffName) ?></strong><br>
                            <span class="table-subtext"><?= e($payout['role']) ?></span>
                        </td>
                        <td><?= e(date('d/m/Y - H:i:s', strtotime((string) $payout['payout_date']))) ?></td>
                        <td><?= e(number_format((float) $payout['amount'], 2)) ?></td>
                        <td><span class="status-pill status-<?= e($payout['status']) ?>"><?= e(ucfirst($payout['status'])) ?></span></td>
                        <td><?= e($payout['posted_at'] !== null ? date('d/m/Y - H:i:s', strtotime((string) $payout['posted_at'])) : 'Not posted') ?></td>
                        <td><?= e($payout['import_row_id'] !== null ? ('Import row #'.$payout['import_row_id']) : 'Suggested / manual') ?></td>
                        <td><?= e((string) ($payout['comment'] ?: '')) ?></td>
                        <td>
                            <div class="row-actions">
                                <?php if ($payout['status'] === 'draft') { ?>
                                    <a class="button button-ghost" href="<?= e(app_url('admin/payout-edit.php?id='.(int) $payout['id'])) ?>">Edit</a>
                                    <form method="post">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="post_payout">
                                        <input type="hidden" name="payout_id" value="<?= e((string) $payout['id']) ?>">
                                        <button type="submit" class="button button-ghost">Post</button>
                                    </form>
                                <?php } ?>
                                <?php if ($payout['status'] !== 'void') { ?>
                                    <form method="post">
                                        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="void_payout">
                                        <input type="hidden" name="payout_id" value="<?= e((string) $payout['id']) ?>">
                                        <button type="submit" class="button button-ghost">Void</button>
                                    </form>
                                <?php } ?>
                                <?php if ($payout['status'] === 'void') { ?>
                                    <span class="table-subtext">No action</span>
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

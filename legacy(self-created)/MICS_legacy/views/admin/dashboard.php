<?php declare(strict_types=1); ?>
<?php
$snapshot = $summary['current_snapshot'];
$students = $snapshot['students'];
$months = $summary['selected_months'];
$monthComparisons = $summary['month_comparisons'];
$distributions = $summary['current_distributions'];
?>
<section class="page-head">
    <div>
        <h1>Dashboard</h1>
        <p>Reliable system-wide overview based on current operational data and selected month comparisons.</p>
    </div>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <h2>Comparison Window</h2>
            <p class="table-subtext">Choose up to three months. Empty fields fall back to the latest available default months.</p>
        </div>
    </div>
    <form method="get" class="filter-bar">
        <label class="field dashboard-month-field">
            <span>Month 1</span>
            <input type="month" name="month_1" value="<?= e($months[0] ?? '') ?>">
        </label>
        <label class="field dashboard-month-field">
            <span>Month 2</span>
            <input type="month" name="month_2" value="<?= e($months[1] ?? '') ?>">
        </label>
        <label class="field dashboard-month-field">
            <span>Month 3</span>
            <input type="month" name="month_3" value="<?= e($months[2] ?? '') ?>">
        </label>
        <button type="submit" class="button button-primary">Update Dashboard</button>
        <a class="button button-ghost" href="<?= e(app_url('admin/dashboard.php')) ?>">Reset</a>
    </form>
</section>

<section class="dashboard-cluster">
    <div class="dashboard-cluster-head">
        <h2>Current Snapshot</h2>
        <p class="table-subtext">These are current-state counts, not historical reconstructions.</p>
    </div>
    <div class="metrics-grid dashboard-metrics">
        <article class="metric-card metric-card-strong">
            <span class="metric-label">Students Total</span>
            <strong class="metric-value"><?= e((string) $students['total']) ?></strong>
            <span class="metric-note">All active, paused, and archived students now.</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Active Students</span>
            <strong class="metric-value"><?= e((string) $students['active']) ?></strong>
            <span class="metric-note">Currently billable/working set.</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Paused Students</span>
            <strong class="metric-value"><?= e((string) $students['paused']) ?></strong>
            <span class="metric-note">Temporarily stopped, still tracked.</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Archived Students</span>
            <strong class="metric-value"><?= e((string) $students['archived']) ?></strong>
            <span class="metric-note">Historical CRM records kept in the system.</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Active Staff</span>
            <strong class="metric-value"><?= e((string) $snapshot['active_staff']) ?></strong>
            <span class="metric-note">Staff currently available in operations.</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Archived Staff</span>
            <strong class="metric-value"><?= e((string) $snapshot['archived_staff']) ?></strong>
            <span class="metric-note">Hidden from active assignment flows.</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Assignable Plans</span>
            <strong class="metric-value"><?= e((string) $snapshot['assignable_plans']) ?></strong>
            <span class="metric-note">Available for new student assignment.</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Archived Plans</span>
            <strong class="metric-value"><?= e((string) $snapshot['archived_plans']) ?></strong>
            <span class="metric-note">Retained for existing records and history.</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Pending Payments</span>
            <strong class="metric-value"><?= e((string) $snapshot['pending_payments']) ?></strong>
            <span class="metric-note">Created but not yet confirmed.</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Confirmed Payments</span>
            <strong class="metric-value"><?= e((string) $snapshot['confirmed_payments']) ?></strong>
            <span class="metric-note">Reliable incoming payment count in business records.</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Statement Queue</span>
            <strong class="metric-value"><?= e((string) $snapshot['statement_queue_rows']) ?></strong>
            <span class="metric-note">Imported bank rows still unresolved.</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Statement Batches</span>
            <strong class="metric-value"><?= e((string) $snapshot['statement_batches']) ?></strong>
            <span class="metric-note">Uploaded CSV statement files in history.</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Draft Payouts</span>
            <strong class="metric-value"><?= e((string) $snapshot['draft_payouts']) ?></strong>
            <span class="metric-note">Suggested or prepared, not yet sent.</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">Posted Journal Entries</span>
            <strong class="metric-value"><?= e((string) $snapshot['posted_journal_entries']) ?></strong>
            <span class="metric-note">Accounting entries already posted.</span>
        </article>
    </div>
</section>

<section class="dashboard-cluster">
    <div class="dashboard-cluster-head">
        <h2>Month Comparison</h2>
        <p class="table-subtext">This section compares only metrics already represented clearly by current tables.</p>
    </div>
    <div class="table-wrap">
        <table class="data-table dashboard-compare-table">
            <thead>
            <tr>
                <th>Metric</th>
                <?php foreach ($monthComparisons as $month): ?>
                    <th><?= e($month['label']) ?></th>
                <?php endforeach; ?>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><strong>New Students Joined</strong><br><span class="table-subtext">Students created with joined date in the month.</span></td>
                <?php foreach ($monthComparisons as $month): ?>
                    <td><?= e((string) $month['student_joined']) ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>Confirmed Payments</strong><br><span class="table-subtext">Count and amount from `payments.status = confirmed`.</span></td>
                <?php foreach ($monthComparisons as $month): ?>
                    <td>
                        <?= e((string) $month['confirmed_payments_count']) ?><br>
                        <span class="table-subtext"><?= e(number_format((float) $month['confirmed_payments_amount'], 2)) ?></span>
                    </td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>Pending Payments</strong><br><span class="table-subtext">Draft or unresolved business payment records.</span></td>
                <?php foreach ($monthComparisons as $month): ?>
                    <td>
                        <?= e((string) $month['pending_payments_count']) ?><br>
                        <span class="table-subtext"><?= e(number_format((float) $month['pending_payments_amount'], 2)) ?></span>
                    </td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>Imported Batches</strong><br><span class="table-subtext">Bank CSV files uploaded during the month.</span></td>
                <?php foreach ($monthComparisons as $month): ?>
                    <td><?= e((string) $month['import_batches']) ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>Imported Rows</strong><br><span class="table-subtext">Raw bank rows added to the system.</span></td>
                <?php foreach ($monthComparisons as $month): ?>
                    <td><?= e((string) $month['import_rows']) ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>Statement Queue Rows</strong><br><span class="table-subtext">Rows still `new` or `unmatched` from that month’s imports.</span></td>
                <?php foreach ($monthComparisons as $month): ?>
                    <td><?= e((string) $month['import_queue_rows']) ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>Draft-Created Rows</strong><br><span class="table-subtext">Imported rows already turned into payment drafts.</span></td>
                <?php foreach ($monthComparisons as $month): ?>
                    <td><?= e((string) $month['draft_created_rows']) ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>Posted Expenses</strong><br><span class="table-subtext">Count and amount from `expenses.status = posted`.</span></td>
                <?php foreach ($monthComparisons as $month): ?>
                    <td>
                        <?= e((string) $month['posted_expenses_count']) ?><br>
                        <span class="table-subtext"><?= e(number_format((float) $month['posted_expenses_amount'], 2)) ?></span>
                    </td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>Draft Payouts</strong><br><span class="table-subtext">Prepared payout amount before send/validation.</span></td>
                <?php foreach ($monthComparisons as $month): ?>
                    <td><?= e(number_format((float) $month['draft_payouts_amount'], 2)) ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>Posted Payouts</strong><br><span class="table-subtext">Actually marked as paid to staff.</span></td>
                <?php foreach ($monthComparisons as $month): ?>
                    <td><?= e(number_format((float) $month['posted_payouts_amount'], 2)) ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>Posted Journal Entries</strong><br><span class="table-subtext">Accounting entries posted in the selected month.</span></td>
                <?php foreach ($monthComparisons as $month): ?>
                    <td><?= e((string) $month['posted_journal_entries']) ?></td>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td><strong>Operational Net Flow</strong><br><span class="table-subtext">Confirmed payments minus posted expenses and posted payouts.</span></td>
                <?php foreach ($monthComparisons as $month): ?>
                    <td><?= e(number_format((float) $month['operational_net_flow'], 2)) ?></td>
                <?php endforeach; ?>
            </tr>
            </tbody>
        </table>
    </div>
</section>

<section class="dashboard-cluster">
    <div class="dashboard-cluster-head">
        <h2>Current Distribution</h2>
        <p class="table-subtext">These sections help you understand where current student load and operational records are concentrated.</p>
    </div>
    <div class="panel-grid dashboard-panels-wide">
        <article class="panel">
            <h2>Students By Plan</h2>
            <?php if ($distributions['students_by_plan'] === []): ?>
                <p>No plans are available yet.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Students</th>
                            <th>Active</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($distributions['students_by_plan'] as $plan): ?>
                            <?php $planStatus = (bool) $plan['is_assignable'] ? 'active' : 'archived'; ?>
                            <tr>
                                <td>
                                    <strong><?= e($plan['name']) ?></strong><br>
                                    <span class="table-subtext"><?= e((bool) $plan['is_assignable'] ? 'Assignable' : 'Archived') ?></span>
                                </td>
                                <td><?= e((string) $plan['total_students']) ?></td>
                                <td><?= e((string) $plan['active_students']) ?></td>
                                <td><span class="status-pill status-<?= e($planStatus) ?>"><?= e((bool) $plan['is_assignable'] ? 'Assignable' : 'Archived') ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </article>

        <article class="panel">
            <h2>Students By Teacher</h2>
            <?php if ($distributions['students_by_teacher'] === []): ?>
                <p>No staff records are available yet.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="data-table">
                        <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Total</th>
                            <th>Active</th>
                            <th>Paused</th>
                            <th>Archived</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($distributions['students_by_teacher'] as $teacher): ?>
                            <?php $teacherName = person_name_from_row($teacher); ?>
                            <tr>
                                <td>
                                    <strong><?= e($teacherName) ?></strong><br>
                                    <span class="table-subtext"><?= e((string) $teacher['status']) ?></span>
                                </td>
                                <td><?= e((string) $teacher['total_students']) ?></td>
                                <td><?= e((string) $teacher['active_students']) ?></td>
                                <td><?= e((string) $teacher['paused_students']) ?></td>
                                <td><?= e((string) $teacher['archived_students']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </article>
    </div>
</section>

<section class="dashboard-cluster">
    <div class="dashboard-cluster-head">
        <h2>Recent Statement Activity</h2>
        <p class="table-subtext">Useful for quickly seeing whether imported bank batches are still waiting for review.</p>
    </div>
    <section class="panel">
        <?php if ($distributions['recent_statement_batches'] === []): ?>
            <p>No statement batches have been imported yet.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Batch</th>
                        <th>Total Rows</th>
                        <th>New Rows</th>
                        <th>Duplicates</th>
                        <th>Open Queue</th>
                        <th>Imported</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($distributions['recent_statement_batches'] as $batch): ?>
                        <tr>
                            <td>
                                <strong><?= e($batch['original_filename']) ?></strong><br>
                                <span class="table-subtext">Batch #<?= e((string) $batch['id']) ?></span>
                            </td>
                            <td><?= e((string) $batch['total_rows']) ?></td>
                            <td><?= e((string) $batch['new_rows']) ?></td>
                            <td><?= e((string) $batch['duplicate_rows']) ?></td>
                            <td><?= e((string) $batch['open_rows']) ?></td>
                            <td><?= e(date('Y-m-d H:i', strtotime((string) $batch['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>

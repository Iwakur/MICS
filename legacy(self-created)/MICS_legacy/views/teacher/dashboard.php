<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1>Teacher Dashboard</h1>
        <p>Focused workspace for your students, collections, and payout analysis for <?= e($summary['period_label']) ?>.</p>
    </div>
</section>

<section class="metrics-grid teacher-metrics">
    <article class="metric-card">
        <span class="metric-label">Active Students</span>
        <strong class="metric-value"><?= e((string) $summary['active_students']) ?></strong>
    </article>
    <article class="metric-card">
        <span class="metric-label">Suggested Payout</span>
        <strong class="metric-value"><?= e(number_format((float) $summary['planned_payout'], 2)) ?></strong>
    </article>
    <article class="metric-card">
        <span class="metric-label">Posted Payout</span>
        <strong class="metric-value"><?= e(number_format((float) $summary['posted_payout'], 2)) ?></strong>
    </article>
    <article class="metric-card">
        <span class="metric-label">Confirmed Payments</span>
        <strong class="metric-value"><?= e(number_format((float) $summary['confirmed_payments'], 2)) ?></strong>
    </article>
    <article class="metric-card">
        <span class="metric-label">Posted Charges</span>
        <strong class="metric-value"><?= e(number_format((float) $summary['posted_charges'], 2)) ?></strong>
    </article>
    <article class="metric-card">
        <span class="metric-label">Collection Gap</span>
        <strong class="metric-value"><?= e(number_format((float) $summary['collection_gap'], 2)) ?></strong>
    </article>
</section>

<section class="panel-grid">
    <article class="panel">
        <h2>Current Snapshot</h2>
        <div class="profile-grid">
            <div class="profile-field">
                <span>Total Students</span>
                <strong><?= e((string) $summary['total_students']) ?></strong>
            </div>
            <div class="profile-field">
                <span>Paused Students</span>
                <strong><?= e((string) $summary['paused_students']) ?></strong>
            </div>
            <div class="profile-field">
                <span>Archived Students</span>
                <strong><?= e((string) $summary['archived_students']) ?></strong>
            </div>
            <div class="profile-field">
                <span>Collection Rate</span>
                <strong><?= e($summary['collection_rate'] !== null ? number_format((float) $summary['collection_rate'], 1).'%' : 'N/A') ?></strong>
            </div>
            <div class="profile-field">
                <span>Joined This Month</span>
                <strong><?= e((string) $summary['joined_this_month']) ?></strong>
            </div>
            <div class="profile-field">
                <span>Analysis Month</span>
                <strong><?= e($summary['period_label']) ?></strong>
            </div>
        </div>
    </article>
    <article class="panel">
        <h2>Month-over-Month Trends</h2>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Metric</th>
                    <th><?= e($summary['previous_period_label']) ?></th>
                    <th><?= e($summary['period_label']) ?></th>
                    <th>Delta</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>Joined Students</td>
                    <td><?= e((string) $summary['trends']['joined_students']['previous']) ?></td>
                    <td><?= e((string) $summary['trends']['joined_students']['current']) ?></td>
                    <td><?= e((string) $summary['trends']['joined_students']['delta']) ?></td>
                </tr>
                <tr>
                    <td>Confirmed Payments</td>
                    <td><?= e(number_format((float) $summary['trends']['confirmed_payments']['previous'], 2)) ?></td>
                    <td><?= e(number_format((float) $summary['trends']['confirmed_payments']['current'], 2)) ?></td>
                    <td><?= e(number_format((float) $summary['trends']['confirmed_payments']['delta'], 2)) ?></td>
                </tr>
                <tr>
                    <td>Posted Charges</td>
                    <td><?= e(number_format((float) $summary['trends']['posted_charges']['previous'], 2)) ?></td>
                    <td><?= e(number_format((float) $summary['trends']['posted_charges']['current'], 2)) ?></td>
                    <td><?= e(number_format((float) $summary['trends']['posted_charges']['delta'], 2)) ?></td>
                </tr>
                <tr>
                    <td>Posted Payout</td>
                    <td><?= e(number_format((float) $summary['trends']['posted_payout']['previous'], 2)) ?></td>
                    <td><?= e(number_format((float) $summary['trends']['posted_payout']['current'], 2)) ?></td>
                    <td><?= e(number_format((float) $summary['trends']['posted_payout']['delta'], 2)) ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </article>
</section>

<section class="panel-grid">
    <article class="panel">
        <h2>Top Contributing Students</h2>
        <?php if ($summary['top_contributors'] === []) { ?>
            <p>No active students are contributing to this month’s suggested payout.</p>
        <?php } else { ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Student</th>
                        <th>Plan</th>
                        <th>Teacher Share</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($summary['top_contributors'] as $student) { ?>
                        <?php $studentName = person_name_from_row($student); ?>
                        <tr>
                            <td><strong><?= e($studentName) ?></strong></td>
                            <td><?= e($student['plan_name']) ?></td>
                            <td><?= e(number_format((float) $student['monthly_teacher_share'], 2)) ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </article>

    <article class="panel">
        <h2>Recent Student Activity</h2>
        <?php if ($summary['recent_students'] === []) { ?>
            <p>No student history is available yet.</p>
        <?php } else { ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Student</th>
                        <th>Status</th>
                        <th>Plan</th>
                        <th>Joined</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($summary['recent_students'] as $student) { ?>
                        <?php $studentName = person_name_from_row($student); ?>
                        <tr>
                            <td><strong><?= e($studentName) ?></strong></td>
                            <td><span class="status-pill status-<?= e($student['status']) ?>"><?= e(ucfirst($student['status'])) ?></span></td>
                            <td><?= e($student['plan_name']) ?></td>
                            <td><?= e(date('d/m/Y - H:i:s', strtotime((string) $student['joined_at']))) ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } ?>
    </article>
</section>

<section class="panel">
    <h2>Recent Payout History</h2>
    <?php if ($summary['recent_payouts'] === []) { ?>
        <p>No payout records have been saved yet.</p>
    <?php } else { ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Posted</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($summary['recent_payouts'] as $payout) { ?>
                    <tr>
                        <td><?= e(date('d/m/Y - H:i:s', strtotime((string) $payout['payout_date']))) ?></td>
                        <td><?= e(number_format((float) $payout['amount'], 2)) ?></td>
                        <td><span class="status-pill status-<?= e($payout['status']) ?>"><?= e(ucfirst($payout['status'])) ?></span></td>
                        <td><?= e($payout['posted_at'] !== null ? date('d/m/Y - H:i:s', strtotime((string) $payout['posted_at'])) : 'Not posted') ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</section>

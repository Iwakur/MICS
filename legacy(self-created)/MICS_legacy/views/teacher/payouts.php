<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1>My Payouts</h1>
        <p>Current-month suggested payout plus saved payout history.</p>
    </div>
</section>

<section class="metrics-grid teacher-metrics">
    <article class="metric-card">
        <span class="metric-label">Month</span>
        <strong class="metric-value"><?= e($periodLabel) ?></strong>
    </article>
    <article class="metric-card">
        <span class="metric-label">Suggested Payout</span>
        <strong class="metric-value"><?= e(number_format((float) $summary['suggested_amount'], 2)) ?></strong>
    </article>
    <article class="metric-card">
        <span class="metric-label">Contributing Students</span>
        <strong class="metric-value"><?= e((string) $summary['student_count']) ?></strong>
    </article>
</section>

<section class="panel">
    <h2>Current-Month Calculation</h2>
    <p>This suggestion is based on active students assigned to you whose join date falls on or before the end of <?= e($periodLabel) ?>.</p>

    <?php if ($students === []): ?>
        <p>No active students currently contribute to this month’s payout.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Student</th>
                    <th>Plan</th>
                    <th>Joined</th>
                    <th>Teacher Share</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($students as $student): ?>
                    <?php $studentName = person_name_from_row($student); ?>
                    <tr>
                        <td><strong><?= e($studentName) ?></strong></td>
                        <td><?= e($student['plan_name']) ?></td>
                        <td><?= e(date('d/m/Y - H:i:s', strtotime((string) $student['joined_at']))) ?></td>
                        <td><?= e(number_format((float) $student['monthly_teacher_share'], 2)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="panel">
    <h2>Saved Payout History</h2>

    <?php if ($history === []): ?>
        <p>No payout records have been created yet.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Posted</th>
                    <th>Comment</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($history as $payout): ?>
                    <tr>
                        <td><?= e(date('d/m/Y - H:i:s', strtotime((string) $payout['payout_date']))) ?></td>
                        <td><?= e(number_format((float) $payout['amount'], 2)) ?></td>
                        <td><span class="status-pill status-<?= e($payout['status']) ?>"><?= e(ucfirst($payout['status'])) ?></span></td>
                        <td><?= e($payout['posted_at'] !== null ? date('d/m/Y - H:i:s', strtotime((string) $payout['posted_at'])) : 'Not posted') ?></td>
                        <td><?= e((string) ($payout['comment'] ?: '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

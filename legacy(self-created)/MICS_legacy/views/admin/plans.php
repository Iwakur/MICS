<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1>Plans</h1>
        <p>Manage the commercial plans that students, pricing, and payout suggestions depend on.</p>
    </div>
    <a class="button button-primary" href="<?= e(app_url('admin/plan-create.php')) ?>">Create Plan</a>
</section>

<section class="panel">
    <form method="get" class="filter-bar">
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search by name or comments">
        <select name="assignable">
            <option value="">All plan statuses</option>
            <?php foreach ($assignableOptions as $option) { ?>
                <option value="<?= e($option) ?>"<?= $assignable === $option ? ' selected' : '' ?>><?= e(ucfirst($option)) ?></option>
            <?php } ?>
        </select>
        <button type="submit" class="button button-ghost">Apply</button>
    </form>
</section>

<section class="panel">
    <?php if ($plans === []) { ?>
        <p>No plans match the current filters.</p>
    <?php } else { ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Plan</th>
                    <th>Pricing</th>
                    <th>Teacher Share</th>
                    <th>Students</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($plans as $plan) { ?>
                    <?php $statusClass = (bool) $plan['is_assignable'] ? 'active' : 'archived'; ?>
                    <?php $monthlyPrice = (float) $plan['lesson_count'] * (float) $plan['lesson_price']; ?>
                    <?php $monthlyTeacherShare = (float) $plan['lesson_count'] * (float) $plan['teacher_share_per_lesson']; ?>
                    <tr>
                        <td>
                            <strong><?= e($plan['name']) ?></strong><br>
                            <span class="table-subtext"><?= e((string) ($plan['comments'] ?: 'No comments')) ?></span>
                        </td>
                        <td>
                            <strong><?= e(number_format($monthlyPrice, 2)) ?></strong><br>
                            <span class="table-subtext"><?= e((string) $plan['lesson_count']) ?> lessons</span><br>
                            <span class="table-subtext"><?= e(number_format((float) $plan['lesson_price'], 2)) ?> per lesson</span>
                        </td>
                        <td>
                            <strong><?= e(number_format($monthlyTeacherShare, 2)) ?></strong><br>
                            <span class="table-subtext"><?= e(number_format((float) $plan['teacher_share_per_lesson'], 2)) ?> per lesson</span>
                        </td>
                        <td><?= e((string) $plan['total_students']) ?></td>
                        <td>
                            <span class="status-pill status-<?= e($statusClass) ?>">
                                <?= e((bool) $plan['is_assignable'] ? 'Assignable' : 'Archived') ?>
                            </span>
                        </td>
                        <td>
                            <div class="row-actions">
                                <a class="button button-ghost" href="<?= e(app_url('admin/plan-edit.php?id='.(int) $plan['id'])) ?>">Edit</a>
                                <form method="post">
                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="plan_id" value="<?= e((string) $plan['id']) ?>">
                                    <input type="hidden" name="assignable_state" value="<?= e((bool) $plan['is_assignable'] ? 'archived' : 'assignable') ?>">
                                    <button type="submit" class="button button-ghost">
                                        <?= (bool) $plan['is_assignable'] ? 'Archive' : 'Activate' ?>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</section>

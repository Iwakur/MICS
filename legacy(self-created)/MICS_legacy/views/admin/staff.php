<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1>Staff</h1>
        <p>Manage active and archived staff records that students and payouts depend on.</p>
    </div>
    <a class="button button-primary" href="<?= e(app_url('admin/staff-create.php')) ?>">Create Staff</a>
</section>

<section class="panel">
    <form method="get" class="filter-bar">
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search by name, role, phone, or email">
        <select name="status">
            <option value="">All statuses</option>
            <?php foreach ($statuses as $staffStatus): ?>
                <option value="<?= e($staffStatus) ?>"<?= $status === $staffStatus ? ' selected' : '' ?>><?= e(ucfirst($staffStatus)) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="button button-ghost">Apply</button>
    </form>
</section>

<section class="panel">
    <?php if ($staff === []): ?>
        <p>No staff records match the current filters.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Staff</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($staff as $staffMember): ?>
                    <?php $staffName = person_name_from_row($staffMember); ?>
                    <tr>
                        <td><strong><?= e($staffName) ?></strong></td>
                        <td><?= e($staffMember['role']) ?></td>
                        <td><span class="status-pill status-<?= e($staffMember['status']) ?>"><?= e(ucfirst($staffMember['status'])) ?></span></td>
                        <td><?= e((string) ($staffMember['phone'] ?: $staffMember['email'] ?: 'No contact')) ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="button button-ghost" href="<?= e(app_url('admin/staff-edit.php?id=' . (int) $staffMember['id'])) ?>">Edit</a>
                                <form method="post">
                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="staff_id" value="<?= e((string) $staffMember['id']) ?>">
                                    <input type="hidden" name="status" value="<?= e($staffMember['status'] === 'archived' ? 'active' : 'archived') ?>">
                                    <button type="submit" class="button button-ghost"><?= $staffMember['status'] === 'archived' ? 'Activate' : 'Archive' ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

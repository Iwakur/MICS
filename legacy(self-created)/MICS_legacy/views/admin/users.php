<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1>Users</h1>
        <p>Manually control usernames, linked staff, login roles, password resets, and active access.</p>
    </div>
    <a class="button button-primary" href="<?= e(app_url('admin/user-create.php')) ?>">Create User</a>
</section>

<section class="panel">
    <form method="get" class="filter-bar">
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search by username or linked staff">
        <select name="role">
            <option value="">All roles</option>
            <?php foreach ($roles as $userRole): ?>
                <option value="<?= e($userRole) ?>"<?= $role === $userRole ? ' selected' : '' ?>><?= e(ucfirst($userRole)) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="is_active">
            <option value="">All access states</option>
            <option value="1"<?= $isActive === '1' ? ' selected' : '' ?>>Active</option>
            <option value="0"<?= $isActive === '0' ? ' selected' : '' ?>>Inactive</option>
        </select>
        <select name="staff_id">
            <option value="">All staff</option>
            <?php foreach ($staffOptions as $staffOption): ?>
                <?php $staffName = person_name_from_row($staffOption); ?>
                <option value="<?= e((string) $staffOption['id']) ?>"<?= $staffId === (string) $staffOption['id'] ? ' selected' : '' ?>>
                    <?= e($staffName) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="button button-ghost">Apply</button>
    </form>
</section>

<section class="panel">
    <?php if ($users === []): ?>
        <p>No user accounts match the current filters.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                <tr>
                    <th>User</th>
                    <th>Linked Staff</th>
                    <th>Role</th>
                    <th>Access</th>
                    <th>Last Login</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <?php $staffName = person_name_from_row($user, 'staff_'); ?>
                    <tr>
                        <td>
                            <strong><?= e($user['username']) ?></strong><br>
                            <span class="table-subtext">Created <?= e(date('Y-m-d H:i', strtotime((string) $user['created_at']))) ?></span>
                        </td>
                        <td>
                            <strong><?= e($staffName !== '' ? $staffName : 'Unlinked') ?></strong><br>
                            <span class="table-subtext"><?= e((string) ($user['staff_role'] ?? 'No linked staff')) ?><?= ($user['staff_status'] ?? null) === 'archived' ? ' · archived' : '' ?></span>
                        </td>
                        <td><span class="status-pill"><?= e(ucfirst($user['role'])) ?></span></td>
                        <td><span class="status-pill status-<?= e($user['is_active'] ? 'active' : 'archived') ?>"><?= e($user['is_active'] ? 'Active' : 'Inactive') ?></span></td>
                        <td><?= e($user['last_login_at'] !== null ? date('Y-m-d H:i:s', strtotime((string) $user['last_login_at'])) : 'Never') ?></td>
                        <td><?= e(date('Y-m-d H:i:s', strtotime((string) $user['updated_at']))) ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="button button-ghost" href="<?= e(app_url('admin/user-edit.php?id=' . (int) $user['id'])) ?>">Edit</a>
                                <a class="button button-ghost" href="<?= e(app_url('admin/user-reset-password.php?id=' . (int) $user['id'])) ?>">Reset Password</a>
                                <form method="post">
                                    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="user_id" value="<?= e((string) $user['id']) ?>">
                                    <input type="hidden" name="is_active" value="<?= e($user['is_active'] ? '0' : '1') ?>">
                                    <button type="submit" class="button button-ghost"><?= $user['is_active'] ? 'Deactivate' : 'Activate' ?></button>
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

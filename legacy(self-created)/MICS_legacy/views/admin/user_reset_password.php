<?php declare(strict_types=1); ?>
<?php $staffName = person_name_from_row($user, 'staff_'); ?>
<section class="page-head">
    <div>
        <h1><?= e($pageTitle) ?></h1>
        <p>Use a separate deliberate action when restoring the starter password for a user.</p>
    </div>
    <a class="button button-ghost" href="<?= e($usersBackLink) ?>">Back to Users</a>
</section>

<section class="panel stack">
    <p><strong>User:</strong> <?= e($user['username']) ?></p>
    <p><strong>Linked staff:</strong> <?= e($staffName !== '' ? $staffName : 'Unlinked') ?></p>
    <p><strong>Role:</strong> <?= e(ucfirst($user['role'])) ?></p>
    <p><strong>Current access:</strong> <?= e($user['is_active'] ? 'Active' : 'Inactive') ?></p>
    <p><strong>Reset target password:</strong> <?= e($defaultPassword) ?></p>

    <form method="post" action="<?= e(app_url('admin/user-reset-password.php?id=' . (int) $user['id'])) ?>" class="form-actions">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <button type="submit" class="button button-primary">Reset Password</button>
        <a class="button button-ghost" href="<?= e($usersBackLink) ?>">Cancel</a>
    </form>
</section>

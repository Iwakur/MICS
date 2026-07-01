<?php declare(strict_types=1); ?>
<?php $errors = form_errors(); ?>

<div class="form-grid">
    <label class="field">
        <span>Linked Staff</span>
        <select name="staff_id" required>
            <option value="">Select a staff member</option>
            <?php foreach ($staffOptions as $staffOption): ?>
                <?php $staffName = person_name_from_row($staffOption); ?>
                <option value="<?= e((string) $staffOption['id']) ?>"<?= $values['staff_id'] === (string) $staffOption['id'] ? ' selected' : '' ?>>
                    <?= e($staffName) ?><?= $staffOption['status'] === 'archived' ? ' (archived)' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['staff_id'])): ?><small class="field-error"><?= e($errors['staff_id']) ?></small><?php endif; ?>
    </label>

    <label class="field">
        <span>Username</span>
        <input type="text" name="username" value="<?= e($values['username']) ?>" required>
        <?php if (isset($errors['username'])): ?><small class="field-error"><?= e($errors['username']) ?></small><?php endif; ?>
    </label>

    <label class="field">
        <span>Role</span>
        <select name="role">
            <?php foreach ($roles as $userRole): ?>
                <option value="<?= e($userRole) ?>"<?= $values['role'] === $userRole ? ' selected' : '' ?>><?= e(ucfirst($userRole)) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['role'])): ?><small class="field-error"><?= e($errors['role']) ?></small><?php endif; ?>
    </label>

    <label class="field">
        <span>Access</span>
        <select name="is_active">
            <option value="1"<?= $values['is_active'] === '1' ? ' selected' : '' ?>>Active</option>
            <option value="0"<?= $values['is_active'] === '0' ? ' selected' : '' ?>>Inactive</option>
        </select>
        <?php if (isset($errors['is_active'])): ?><small class="field-error"><?= e($errors['is_active']) ?></small><?php endif; ?>
    </label>
</div>

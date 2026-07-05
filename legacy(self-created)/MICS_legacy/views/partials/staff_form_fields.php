<?php declare(strict_types=1); ?>
<?php $errors = form_errors(); ?>

<div class="form-grid">
    <label class="field">
        <span>Role</span>
        <input type="text" name="role" value="<?= e($values['role']) ?>" required>
        <?php if (isset($errors['role'])) { ?><small class="field-error"><?= e($errors['role']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>First Name</span>
        <input type="text" name="first_name" value="<?= e($values['first_name']) ?>" required>
        <?php if (isset($errors['first_name'])) { ?><small class="field-error"><?= e($errors['first_name']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Family Name</span>
        <input type="text" name="family_name" value="<?= e($values['family_name']) ?>">
    </label>

    <label class="field">
        <span>Father Name</span>
        <input type="text" name="father_name" value="<?= e($values['father_name']) ?>">
    </label>

    <label class="field">
        <span>Status</span>
        <select name="status">
            <?php foreach ($statuses as $staffStatus) { ?>
                <option value="<?= e($staffStatus) ?>"<?= $values['status'] === $staffStatus ? ' selected' : '' ?>><?= e(ucfirst($staffStatus)) ?></option>
            <?php } ?>
        </select>
        <?php if (isset($errors['status'])) { ?><small class="field-error"><?= e($errors['status']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Payout Card Number</span>
        <input type="text" name="payout_card_number" value="<?= e($values['payout_card_number']) ?>">
    </label>

    <label class="field">
        <span>Fixed Salary Amount</span>
        <input type="number" name="fixed_salary_amount" value="<?= e($values['fixed_salary_amount']) ?>" min="0" step="0.01">
        <?php if (isset($errors['fixed_salary_amount'])) { ?><small class="field-error"><?= e($errors['fixed_salary_amount']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Phone</span>
        <input type="text" name="phone" value="<?= e($values['phone']) ?>">
    </label>

    <label class="field">
        <span>Email</span>
        <input type="email" name="email" value="<?= e($values['email']) ?>">
        <?php if (isset($errors['email'])) { ?><small class="field-error"><?= e($errors['email']) ?></small><?php } ?>
    </label>
</div>

<label class="field">
    <span>Comments</span>
    <textarea name="comments" rows="4"><?= e($values['comments']) ?></textarea>
</label>

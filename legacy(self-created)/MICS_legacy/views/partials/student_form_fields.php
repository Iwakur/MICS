<?php declare(strict_types=1); ?>
<?php $errors = form_errors(); ?>

<div class="form-grid">
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
        <span>Phone</span>
        <input type="text" name="phone" value="<?= e($values['phone']) ?>">
    </label>

    <label class="field">
        <span>Email</span>
        <input type="email" name="email" value="<?= e($values['email']) ?>">
        <?php if (isset($errors['email'])) { ?><small class="field-error"><?= e($errors['email']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Status</span>
        <select name="status">
            <?php foreach ($statuses as $studentStatus) { ?>
                <option value="<?= e($studentStatus) ?>"<?= $values['status'] === $studentStatus ? ' selected' : '' ?>><?= e(ucfirst($studentStatus)) ?></option>
            <?php } ?>
        </select>
        <?php if (isset($errors['status'])) { ?><small class="field-error"><?= e($errors['status']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Plan</span>
        <select name="plan_id" required>
            <option value="">Select a plan</option>
            <?php foreach ($plans as $plan) { ?>
                <option value="<?= e((string) $plan['id']) ?>"<?= $values['plan_id'] === (string) $plan['id'] ? ' selected' : '' ?>>
                    <?= e($plan['name']) ?>
                </option>
            <?php } ?>
        </select>
        <?php if (isset($errors['plan_id'])) { ?><small class="field-error"><?= e($errors['plan_id']) ?></small><?php } ?>
    </label>

    <?php if ($allowStaffAssignment) { ?>
        <label class="field">
            <span>Teacher</span>
            <select name="staff_id" required>
                <option value="">Select a teacher</option>
                <?php foreach ($staffOptions as $staffOption) { ?>
                    <?php $staffLabel = person_name_from_row($staffOption); ?>
                    <option value="<?= e((string) $staffOption['id']) ?>"<?= $values['staff_id'] === (string) $staffOption['id'] ? ' selected' : '' ?>>
                        <?= e($staffLabel) ?>
                    </option>
                <?php } ?>
            </select>
            <?php if (isset($errors['staff_id'])) { ?><small class="field-error"><?= e($errors['staff_id']) ?></small><?php } ?>
        </label>
    <?php } ?>

    <label class="field">
        <span>Discount Amount</span>
        <input type="number" step="0.01" min="0" name="discount_amount" value="<?= e($values['discount_amount']) ?>">
        <?php if (isset($errors['discount_amount'])) { ?><small class="field-error"><?= e($errors['discount_amount']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Joined At</span>
        <input type="datetime-local" name="joined_at" value="<?= e($values['joined_at']) ?>" required>
        <?php if (isset($errors['joined_at'])) { ?><small class="field-error"><?= e($errors['joined_at']) ?></small><?php } ?>
    </label>
</div>

<label class="field">
    <span>Comments</span>
    <textarea name="comments" rows="4"><?= e($values['comments']) ?></textarea>
</label>

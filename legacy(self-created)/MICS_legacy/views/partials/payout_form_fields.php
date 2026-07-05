<?php declare(strict_types=1); ?>
<?php $errors = form_errors(); ?>

<div class="form-grid">
    <label class="field">
        <span>Staff</span>
        <select name="staff_id" required>
            <option value="">Choose staff</option>
            <?php foreach ($staff as $staffMember) { ?>
                <?php $staffName = person_name_from_row($staffMember); ?>
                <option value="<?= e((string) $staffMember['id']) ?>"<?= $values['staff_id'] === (string) $staffMember['id'] ? ' selected' : '' ?>>
                    <?= e($staffName) ?>
                </option>
            <?php } ?>
        </select>
        <?php if (isset($errors['staff_id'])) { ?><small class="field-error"><?= e($errors['staff_id']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Payout Date</span>
        <input type="datetime-local" name="payout_date" value="<?= e($values['payout_date']) ?>" required>
        <?php if (isset($errors['payout_date'])) { ?><small class="field-error"><?= e($errors['payout_date']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Amount</span>
        <input type="number" name="amount" value="<?= e($values['amount']) ?>" min="0.01" step="0.01" required>
        <?php if (isset($errors['amount'])) { ?><small class="field-error"><?= e($errors['amount']) ?></small><?php } ?>
    </label>
</div>

<label class="field">
    <span>Comment</span>
    <textarea name="comment" rows="4"><?= e($values['comment']) ?></textarea>
</label>

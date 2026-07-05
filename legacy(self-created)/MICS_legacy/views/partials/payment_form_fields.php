<?php declare(strict_types=1); ?>
<?php $errors = form_errors(); ?>

<div class="form-grid">
    <label class="field">
        <span>Student</span>
        <select name="student_id" required>
            <option value="">Choose student</option>
            <?php foreach ($students as $student) { ?>
                <?php $studentName = person_name_from_row($student); ?>
                <option value="<?= e((string) $student['id']) ?>"<?= $values['student_id'] === (string) $student['id'] ? ' selected' : '' ?>>
                    <?= e($studentName !== '' ? $studentName : ('Student #'.$student['id'])) ?>
                </option>
            <?php } ?>
        </select>
        <?php if (isset($errors['student_id'])) { ?><small class="field-error"><?= e($errors['student_id']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Payment Date</span>
        <input type="datetime-local" name="payment_date" value="<?= e($values['payment_date']) ?>" required>
        <?php if (isset($errors['payment_date'])) { ?><small class="field-error"><?= e($errors['payment_date']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Amount</span>
        <input type="number" name="amount" value="<?= e($values['amount']) ?>" min="0.01" step="0.01" required>
        <?php if (isset($errors['amount'])) { ?><small class="field-error"><?= e($errors['amount']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Method</span>
        <input type="text" name="method" value="<?= e($values['method']) ?>" required>
        <?php if (isset($errors['method'])) { ?><small class="field-error"><?= e($errors['method']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Source</span>
        <input type="text" name="source" value="<?= e($values['source']) ?>" required>
        <?php if (isset($errors['source'])) { ?><small class="field-error"><?= e($errors['source']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>External Reference</span>
        <input type="text" name="external_reference" value="<?= e($values['external_reference']) ?>">
    </label>

    <label class="field">
        <span>Status</span>
        <select name="status">
            <?php foreach ($statuses as $paymentStatus) { ?>
                <option value="<?= e($paymentStatus) ?>"<?= $values['status'] === $paymentStatus ? ' selected' : '' ?>><?= e(ucfirst($paymentStatus)) ?></option>
            <?php } ?>
        </select>
        <?php if (isset($errors['status'])) { ?><small class="field-error"><?= e($errors['status']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Covered Month</span>
        <input type="month" name="covered_month" value="<?= e($values['covered_month']) ?>">
        <?php if (isset($errors['covered_month'])) { ?><small class="field-error"><?= e($errors['covered_month']) ?></small><?php } ?>
    </label>
</div>

<label class="field">
    <span>Comment</span>
    <textarea name="comment" rows="4"><?= e($values['comment']) ?></textarea>
</label>

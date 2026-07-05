<?php declare(strict_types=1); ?>
<?php $errors = form_errors(); ?>

<div class="form-grid">
    <label class="field">
        <span>Expense Date</span>
        <input type="datetime-local" name="expense_date" value="<?= e($values['expense_date']) ?>" required>
        <?php if (isset($errors['expense_date'])) { ?><small class="field-error"><?= e($errors['expense_date']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Category</span>
        <select name="category_id" required>
            <option value="">Choose category</option>
            <?php foreach ($categories as $category) { ?>
                <option value="<?= e((string) $category['id']) ?>"<?= $values['category_id'] === (string) $category['id'] ? ' selected' : '' ?>>
                    <?= e($category['name'].' ('.$category['code'].')') ?>
                </option>
            <?php } ?>
        </select>
        <?php if (isset($errors['category_id'])) { ?><small class="field-error"><?= e($errors['category_id']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Amount</span>
        <input type="number" name="amount" value="<?= e($values['amount']) ?>" min="0.01" step="0.01" required>
        <?php if (isset($errors['amount'])) { ?><small class="field-error"><?= e($errors['amount']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Paid From Account</span>
        <select name="paid_from_account_id" required>
            <option value="">Choose account</option>
            <?php foreach ($accounts as $account) { ?>
                <option value="<?= e((string) $account['id']) ?>"<?= $values['paid_from_account_id'] === (string) $account['id'] ? ' selected' : '' ?>>
                    <?= e($account['code'].' - '.$account['name']) ?>
                </option>
            <?php } ?>
        </select>
        <?php if (isset($errors['paid_from_account_id'])) { ?><small class="field-error"><?= e($errors['paid_from_account_id']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Staff Link</span>
        <select name="staff_id">
            <option value="">No staff link</option>
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
        <span>Status</span>
        <select name="status">
            <?php foreach ($statuses as $expenseStatus) { ?>
                <option value="<?= e($expenseStatus) ?>"<?= $values['status'] === $expenseStatus ? ' selected' : '' ?>><?= e(ucfirst($expenseStatus)) ?></option>
            <?php } ?>
        </select>
        <?php if (isset($errors['status'])) { ?><small class="field-error"><?= e($errors['status']) ?></small><?php } ?>
    </label>
    
    <label class="field">
        <span>Reason</span>
        <input type="text" name="reason" value="<?= e($values['reason']) ?>">
    </label>
</div>

<label class="field">
    <span>Description</span>
    <textarea name="description" rows="4"><?= e($values['description']) ?></textarea>
</label>

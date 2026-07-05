<?php declare(strict_types=1); ?>
<?php $errors = form_errors(); ?>

<div class="form-grid">
    <label class="field">
        <span>Name</span>
        <input type="text" name="name" value="<?= e($values['name']) ?>" required>
        <?php if (isset($errors['name'])) { ?><small class="field-error"><?= e($errors['name']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Lesson Count</span>
        <input type="number" name="lesson_count" value="<?= e($values['lesson_count']) ?>" min="0" step="0.01" required>
        <?php if (isset($errors['lesson_count'])) { ?><small class="field-error"><?= e($errors['lesson_count']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Lesson Price</span>
        <input type="number" name="lesson_price" value="<?= e($values['lesson_price']) ?>" min="0" step="0.01" required>
        <?php if (isset($errors['lesson_price'])) { ?><small class="field-error"><?= e($errors['lesson_price']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Teacher Share Per Lesson</span>
        <input type="number" name="teacher_share_per_lesson" value="<?= e($values['teacher_share_per_lesson']) ?>" min="0" step="0.01" required>
        <?php if (isset($errors['teacher_share_per_lesson'])) { ?><small class="field-error"><?= e($errors['teacher_share_per_lesson']) ?></small><?php } ?>
    </label>

    <label class="field">
        <span>Status</span>
        <select name="assignable_state">
            <?php foreach ($assignableOptions as $option) { ?>
                <option value="<?= e($option) ?>"<?= $values['assignable_state'] === $option ? ' selected' : '' ?>><?= e(ucfirst($option)) ?></option>
            <?php } ?>
        </select>
        <?php if (isset($errors['assignable_state'])) { ?><small class="field-error"><?= e($errors['assignable_state']) ?></small><?php } ?>
    </label>
</div>

<label class="field">
    <span>Comments</span>
    <textarea name="comments" rows="4"><?= e($values['comments']) ?></textarea>
</label>

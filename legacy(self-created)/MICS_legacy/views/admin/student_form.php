<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1><?= e($pageTitle) ?></h1>
        <p>Manage student core CRM details without touching photos or accounting workflows yet.</p>
    </div>
    <a class="button button-ghost" href="<?= e($studentsBackLink) ?>">Back to Students</a>
</section>

<section class="panel">
    <form method="post" action="<?= e($formAction) ?>" class="stack">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <?php require base_path('views/partials/student_form_fields.php'); ?>
        <div class="form-actions">
            <button type="submit" class="button button-primary"><?= e($submitLabel) ?></button>
            <a class="button button-ghost" href="<?= e($studentsBackLink) ?>">Cancel</a>
        </div>
    </form>
</section>

<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1><?= e($pageTitle) ?></h1>
        <p>Maintain staff identity, role, payout destination, and status in one place.</p>
    </div>
    <a class="button button-ghost" href="<?= e($staffBackLink) ?>">Back to Staff</a>
</section>

<section class="panel">
    <form method="post" action="<?= e($formAction) ?>" class="stack">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <?php require base_path('views/partials/staff_form_fields.php'); ?>
        <div class="form-actions">
            <button type="submit" class="button button-primary"><?= e($submitLabel) ?></button>
            <a class="button button-ghost" href="<?= e($staffBackLink) ?>">Cancel</a>
        </div>
    </form>
</section>

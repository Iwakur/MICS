<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1><?= e($pageTitle) ?></h1>
        <p>Adjust the draft before money is actually sent. Posted payouts should remain historical truth.</p>
    </div>
    <a class="button button-ghost" href="<?= e($backLink) ?>">Back to Payouts</a>
</section>

<section class="panel">
    <form method="post" action="<?= e($formAction) ?>" class="stack">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <?php require base_path('views/partials/payout_form_fields.php'); ?>
        <div class="form-actions">
            <button type="submit" class="button button-primary"><?= e($submitLabel) ?></button>
            <a class="button button-ghost" href="<?= e($backLink) ?>">Cancel</a>
        </div>
    </form>
</section>

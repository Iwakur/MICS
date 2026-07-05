<?php declare(strict_types=1); ?>
<section class="page-head">
    <div>
        <h1><?= e($pageTitle) ?></h1>
        <p>Manage the login identity separately from the staff record, but keep every user explicitly linked.</p>
    </div>
    <a class="button button-ghost" href="<?= e($usersBackLink) ?>">Back to Users</a>
</section>

<section class="panel">
    <?php if ($showPasswordNotice) { ?>
        <p>The new account will start with the configured default password: <strong><?= e($defaultPassword) ?></strong></p>
    <?php } else { ?>
        <p>Password changes are handled separately through the dedicated reset action.</p>
    <?php } ?>

    <form method="post" action="<?= e($formAction) ?>" class="stack">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <?php require base_path('views/partials/user_form_fields.php'); ?>
        <div class="form-actions">
            <button type="submit" class="button button-primary"><?= e($submitLabel) ?></button>
            <a class="button button-ghost" href="<?= e($usersBackLink) ?>">Cancel</a>
        </div>
    </form>
</section>

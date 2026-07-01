<?php declare(strict_types=1); ?>
<?php if ($success = flash('success')): ?>
    <div class="alert alert-success"><?= e((string) $success) ?></div>
<?php endif; ?>

<?php if ($error = flash('error')): ?>
    <div class="alert alert-error"><?= e((string) $error) ?></div>
<?php endif; ?>

<?php declare(strict_types=1); ?>
<section class="login-card">
    <div class="login-brand">
        <div class="brand-mark"><img src="<?= e(app_url('uploads/meta/ico.jpg')) ?>" alt="MICS' Logo"></div>
        <div>
            <h1><?= e(config('app.name')) ?></h1>
            <p>CRM + ERP solution</p>
            <p>Admin & teacher access</p>
        </div>
    </div>

    <?php if ($error = flash('error')) { ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php } ?>

    <form method="post" class="stack">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">

        <label class="field">
            <span>Username</span>
            <input type="text" name="username" required autocomplete="username">
        </label>

        <label class="field">
            <span>Password</span>
            <input type="password" name="password" required autocomplete="current-password">
        </label>

        <button type="submit" class="button button-primary">Log In</button>
    </form>

    <div class="login-help">
        <div>We suggest changing your password after the first login ;)</div>
    </div>
</section>

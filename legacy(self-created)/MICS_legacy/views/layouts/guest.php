<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($title ?? config('app.name')).' | '.config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset_url('css/app.css')) ?>">
</head>
<body class="guest-shell">
    <main class="guest-main">
        <?php require $contentView; ?>
    </main>
</body>
</html>

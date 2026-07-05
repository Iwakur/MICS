<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(($title ?? 'Teacher').' | '.config('app.name')) ?></title>
    <link rel="stylesheet" href="<?= e(asset_url('css/app.css')) ?>">
</head>
<body class="teacher-shell">
    <?php require base_path('views/partials/teacher_header.php'); ?>
    <div class="app-frame teacher-frame">
        <?php require base_path('views/partials/teacher_sidebar.php'); ?>
        <main class="app-content">
            <?php require base_path('views/partials/flash.php'); ?>
            <?php require $contentView; ?>
        </main>
    </div>
    <script>
        document.querySelectorAll('.js-live-clock').forEach(function (clock) {
            var initial = clock.dataset.now;
            if (!initial) {
                return;
            }

            var value = clock.querySelector('.period-pill-value');
            if (!value) {
                return;
            }

            var current = new Date(initial);
            var format = function (date) {
                var day = String(date.getDate()).padStart(2, '0');
                var month = String(date.getMonth() + 1).padStart(2, '0');
                var year = date.getFullYear();
                var hours = String(date.getHours()).padStart(2, '0');
                var minutes = String(date.getMinutes()).padStart(2, '0');
                var seconds = String(date.getSeconds()).padStart(2, '0');

                return day + '/' + month + '/' + year + ' - ' + hours + ':' + minutes + ':' + seconds;
            };

            value.textContent = format(current);
            window.setInterval(function () {
                current = new Date(current.getTime() + 1000);
                value.textContent = format(current);
            }, 1000);
        });

        document.querySelectorAll('.js-session-clock').forEach(function (clock) {
            var sessionKey = clock.dataset.sessionKey;
            var now = clock.dataset.now;
            if (!sessionKey || !now) {
                return;
            }

            var value = clock.querySelector('.period-pill-value');
            if (!value) {
                return;
            }

            var startedAt = sessionStorage.getItem(sessionKey);
            if (!startedAt) {
                startedAt = now;
                sessionStorage.setItem(sessionKey, startedAt);
            }

            var started = new Date(startedAt);
            var formatDuration = function (totalSeconds) {
                var hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
                var minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
                var seconds = String(totalSeconds % 60).padStart(2, '0');

                return hours + ':' + minutes + ':' + seconds;
            };

            var render = function () {
                var totalSeconds = Math.max(0, Math.floor((Date.now() - started.getTime()) / 1000));
                value.textContent = formatDuration(totalSeconds);
            };

            render();
            window.setInterval(render, 1000);
        });
    </script>
</body>
</html>

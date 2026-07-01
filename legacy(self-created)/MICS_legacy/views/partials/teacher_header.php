<?php declare(strict_types=1); ?>
<?php $authUser = \App\Auth::user(); ?>
<?php $currentDateTime = current_app_datetime(); ?>
<?php $nowIso = $currentDateTime->format(DateTimeInterface::ATOM); ?>
<header class="topbar topbar-teacher">
    <div class="brand-block">
        <div class="brand-mark"><img src="<?= e(app_url('uploads/meta/ico.jpg')) ?>" alt="MICS logo"></div>
        <div>
            <div class="brand-title"><?= e(config('app.name')) ?> Teacher</div>
            <div class="brand-subtitle">Personal workspace</div>
        </div>
    </div>
    <div class="topbar-actions">
        <div class="period-pill js-live-clock" data-now="<?= e($nowIso) ?>">
            <span class="period-pill-label">Current time</span>
            <strong class="period-pill-value"><?= e($currentDateTime->format('d/m/Y - H:i:s')) ?></strong>
        </div>
        <div class="period-pill js-session-clock" data-now="<?= e($nowIso) ?>" data-session-key="mics.teacher.session_started_at">
            <span class="period-pill-label">Session time</span>
            <strong class="period-pill-value">00:00:00</strong>
        </div>
        <a class="user-chip user-chip-link" href="<?= e(app_url('teacher/profile.php')) ?>">
            <span class="avatar avatar-small"><?= e(strtoupper(substr((string) ($authUser['username'] ?? 'T'), 0, 1))) ?></span>
            <div>
                <div class="user-chip-title"><?= e($authUser['username'] ?? 'teacher') ?></div>
                <div class="user-chip-subtitle">Profile</div>
            </div>
        </a>
    </div>
</header>

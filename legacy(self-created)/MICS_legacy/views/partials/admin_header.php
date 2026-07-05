<?php declare(strict_types=1);
use App\Auth;

 ?>
<?php $authUser = Auth::user(); ?>
<?php $currentDateTime = current_app_datetime(); ?>
<?php $nowIso = $currentDateTime->format(DateTimeInterface::ATOM); ?>
<header class="topbar topbar-admin">
    <div class="brand-block">
        <div class="brand-mark"><img src="<?= e(app_url('uploads/meta/ico.jpg')) ?>" alt="MICS logo"></div>
        <div>
            <div class="brand-title"><?= e(config('app.name')) ?> Admin</div>
            <div class="brand-subtitle">Backoffice control</div>
        </div>
    </div>
    <form class="search-bar" action="<?= e(app_url('admin/students.php')) ?>" method="get">
        <input type="search" name="q" placeholder="Search students, staff, payments...">
    </form>
    <div class="topbar-actions">
        <div class="period-pill js-live-clock" data-now="<?= e($nowIso) ?>">
            <span class="period-pill-label">Current time</span>
            <strong class="period-pill-value"><?= e($currentDateTime->format('d/m/Y - H:i:s')) ?></strong>
        </div>
        <div class="period-pill js-session-clock" data-now="<?= e($nowIso) ?>" data-session-key="mics.admin.session_started_at">
            <span class="period-pill-label">Session time</span>
            <strong class="period-pill-value">00:00:00</strong>
        </div>
        <a class="user-chip user-chip-link" href="<?= e(app_url('admin/settings.php')) ?>">
            <span class="avatar avatar-small"><?= e(strtoupper(substr((string) ($authUser['username'] ?? 'A'), 0, 1))) ?></span>
            <div>
                <div class="user-chip-title"><?= e($authUser['username'] ?? 'admin') ?></div>
                <div class="user-chip-subtitle">Settings</div>
            </div>
        </a>
    </div>
</header>

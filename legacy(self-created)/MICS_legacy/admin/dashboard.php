<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

\App\Auth::requireAdmin();

$repository = new \App\Repositories\DashboardRepository();
$selectedMonths = [];

foreach (['month_1', 'month_2', 'month_3'] as $field) {
    $value = trim((string) ($_GET[$field] ?? ''));
    if ($value !== '') {
        $selectedMonths[] = $value;
    }
}

$summary = $repository->adminSummary($selectedMonths);

render('admin/dashboard', [
    'title' => 'Dashboard',
    'summary' => $summary,
], 'admin');

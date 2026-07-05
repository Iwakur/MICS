<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\DashboardRepository;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireAdmin();

$repository = new DashboardRepository;
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

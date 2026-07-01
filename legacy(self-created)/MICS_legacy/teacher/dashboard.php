<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

\App\Auth::requireTeacher();

$staffId = (int) (\App\Auth::user()['staff_id'] ?? 0);
$repository = new \App\Repositories\DashboardRepository();
$summary = $repository->teacherSummary($staffId);

render('teacher/dashboard', [
    'title' => 'Dashboard',
    'summary' => $summary,
], 'teacher');

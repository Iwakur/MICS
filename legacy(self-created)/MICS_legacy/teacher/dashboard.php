<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\DashboardRepository;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireTeacher();

$staffId = (int) (Auth::user()['staff_id'] ?? 0);
$repository = new DashboardRepository;
$summary = $repository->teacherSummary($staffId);

render('teacher/dashboard', [
    'title' => 'Dashboard',
    'summary' => $summary,
], 'teacher');

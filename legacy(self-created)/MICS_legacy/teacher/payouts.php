<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
\App\Auth::requireTeacher();

$staffId = (int) (\App\Auth::user()['staff_id'] ?? 0);
$payoutService = new \App\Services\PayoutService();
$repository = new \App\Repositories\PayoutRepository();
$period = $payoutService->currentMonthPeriod();
$nextMonthStart = $payoutService->asSqlTimestamp($period['next_month_start']);
$summary = $repository->teacherSuggestionSummary($staffId, $nextMonthStart);
$students = $repository->teacherSuggestionStudents($staffId, $nextMonthStart);
$history = $repository->teacherHistory($staffId);

render('teacher/payouts', [
    'title' => 'My Payouts',
    'periodLabel' => $period['label'],
    'summary' => $summary,
    'students' => $students,
    'history' => $history,
], 'teacher');

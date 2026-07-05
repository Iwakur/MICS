<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\PayoutRepository;
use App\Services\PayoutService;

require dirname(__DIR__).'/app/bootstrap.php';
Auth::requireTeacher();

$staffId = (int) (Auth::user()['staff_id'] ?? 0);
$payoutService = new PayoutService;
$repository = new PayoutRepository;
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

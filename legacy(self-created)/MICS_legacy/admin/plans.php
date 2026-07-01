<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

\App\Auth::requireAdmin();

$repository = new \App\Repositories\PlanRepository();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/plans.php');
    }

    $planId = (int) ($_POST['plan_id'] ?? 0);
    $assignableState = trim((string) ($_POST['assignable_state'] ?? ''));
    $plan = $repository->findById($planId);

    if ($plan === null || ! in_array($assignableState, \App\Services\PlanFormService::ASSIGNABLE_OPTIONS, true)) {
        flash('error', 'Unable to update that plan status.');
        redirect('admin/plans.php');
    }

    $repository->update($planId, [
        'name' => $plan['name'],
        'lesson_count' => (float) $plan['lesson_count'],
        'lesson_price' => (float) $plan['lesson_price'],
        'teacher_share_per_lesson' => (float) $plan['teacher_share_per_lesson'],
        'is_assignable' => $assignableState === 'assignable',
        'comments' => $plan['comments'],
    ]);

    flash('success', 'Plan status updated.');
    redirect('admin/plans.php');
}

$search = trim((string) ($_GET['q'] ?? ''));
$assignable = trim((string) ($_GET['assignable'] ?? ''));
$plans = $repository->findForList($search !== '' ? $search : null, $assignable !== '' ? $assignable : null);

render('admin/plans', [
    'title' => 'Plans',
    'plans' => $plans,
    'search' => $search,
    'assignable' => $assignable,
    'assignableOptions' => \App\Services\PlanFormService::ASSIGNABLE_OPTIONS,
], 'admin');

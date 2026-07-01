<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

\App\Auth::requireAdmin();

$planId = (int) ($_GET['id'] ?? 0);
$repository = new \App\Repositories\PlanRepository();
$plan = $repository->findById($planId);

if ($plan === null) {
    flash('error', 'Plan not found.');
    redirect('admin/plans.php');
}

$formService = new \App\Services\PlanFormService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/plan-edit.php?id=' . $planId);
    }

    $result = $formService->validate(
        $_POST,
        $repository->nameExists(trim((string) ($_POST['name'] ?? '')), $planId)
    );

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the plan form.');
        redirect('admin/plan-edit.php?id=' . $planId);
    }

    $repository->update($planId, $result['data']);
    flash('success', 'Plan updated.');
    redirect('admin/plans.php');
}

render('admin/plan_form', [
    'title' => 'Edit Plan',
    'pageTitle' => 'Edit Plan',
    'formAction' => app_url('admin/plan-edit.php?id=' . $planId),
    'submitLabel' => 'Save Plan',
    'plansBackLink' => app_url('admin/plans.php'),
    'values' => $formService->defaults($plan),
    'assignableOptions' => \App\Services\PlanFormService::ASSIGNABLE_OPTIONS,
], 'admin');

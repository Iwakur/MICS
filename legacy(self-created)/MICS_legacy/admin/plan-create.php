<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\PlanRepository;
use App\Services\PlanFormService;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireAdmin();

$repository = new PlanRepository;
$formService = new PlanFormService;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/plan-create.php');
    }

    $result = $formService->validate($_POST, $repository->nameExists(trim((string) ($_POST['name'] ?? ''))));

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the plan form.');
        redirect('admin/plan-create.php');
    }

    $repository->create($result['data']);
    flash('success', 'Plan created.');
    redirect('admin/plans.php');
}

render('admin/plan_form', [
    'title' => 'Create Plan',
    'pageTitle' => 'Create Plan',
    'formAction' => app_url('admin/plan-create.php'),
    'submitLabel' => 'Create Plan',
    'plansBackLink' => app_url('admin/plans.php'),
    'values' => $formService->defaults(),
    'assignableOptions' => PlanFormService::ASSIGNABLE_OPTIONS,
], 'admin');

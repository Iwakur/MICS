<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\StaffRepository;
use App\Services\StaffFormService;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireAdmin();

$repository = new StaffRepository;
$formService = new StaffFormService;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/staff-create.php');
    }

    $result = $formService->validate($_POST);

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the staff form.');
        redirect('admin/staff-create.php');
    }

    $repository->create($result['data']);
    flash('success', 'Staff member created.');
    redirect('admin/staff.php');
}

render('admin/staff_form', [
    'title' => 'Create Staff',
    'pageTitle' => 'Create Staff',
    'formAction' => app_url('admin/staff-create.php'),
    'submitLabel' => 'Create Staff',
    'staffBackLink' => app_url('admin/staff.php'),
    'values' => $formService->defaults(),
    'statuses' => StaffFormService::STATUSES,
], 'admin');

<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

\App\Auth::requireAdmin();

$repository = new \App\Repositories\StaffRepository();
$formService = new \App\Services\StaffFormService();

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
    'statuses' => \App\Services\StaffFormService::STATUSES,
], 'admin');

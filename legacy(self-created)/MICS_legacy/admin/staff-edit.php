<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

\App\Auth::requireAdmin();

$staffId = (int) ($_GET['id'] ?? 0);
$repository = new \App\Repositories\StaffRepository();
$staff = $repository->findById($staffId);

if ($staff === null) {
    flash('error', 'Staff member not found.');
    redirect('admin/staff.php');
}

$formService = new \App\Services\StaffFormService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/staff-edit.php?id=' . $staffId);
    }

    $result = $formService->validate($_POST);

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the staff form.');
        redirect('admin/staff-edit.php?id=' . $staffId);
    }

    $repository->update($staffId, $result['data']);
    flash('success', 'Staff member updated.');
    redirect('admin/staff.php');
}

render('admin/staff_form', [
    'title' => 'Edit Staff',
    'pageTitle' => 'Edit Staff',
    'formAction' => app_url('admin/staff-edit.php?id=' . $staffId),
    'submitLabel' => 'Save Staff',
    'staffBackLink' => app_url('admin/staff.php'),
    'values' => $formService->defaults($staff),
    'statuses' => \App\Services\StaffFormService::STATUSES,
], 'admin');

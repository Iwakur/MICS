<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\StudentRepository;
use App\Services\StudentFormService;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireAdmin();

$repository = new StudentRepository;
$formService = new StudentFormService;
$plans = $repository->activePlans();
$staffOptions = $repository->activeStaff();
$planIds = array_map(static fn (array $plan): int => (int) $plan['id'], $plans);
$staffIds = array_map(static fn (array $staff): int => (int) $staff['id'], $staffOptions);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/student-create.php');
    }

    $result = $formService->validate($_POST, $planIds, $staffIds, true);

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the student form.');
        redirect('admin/student-create.php');
    }

    $repository->create($result['data']);
    flash('success', 'Student created.');
    redirect('admin/students.php');
}

render('admin/student_form', [
    'title' => 'Create Student',
    'pageTitle' => 'Create Student',
    'formAction' => app_url('admin/student-create.php'),
    'submitLabel' => 'Create Student',
    'studentsBackLink' => app_url('admin/students.php'),
    'values' => $formService->defaults(),
    'plans' => $plans,
    'staffOptions' => $staffOptions,
    'statuses' => StudentFormService::STATUSES,
    'allowStaffAssignment' => true,
], 'admin');

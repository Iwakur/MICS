<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

\App\Auth::requireAdmin();

$studentId = (int) ($_GET['id'] ?? 0);
$repository = new \App\Repositories\StudentRepository();
$student = $repository->findByIdForAdmin($studentId);

if ($student === null) {
    flash('error', 'Student not found.');
    redirect('admin/students.php');
}

$formService = new \App\Services\StudentFormService();
$plans = $repository->activePlansIncluding((int) $student['plan_id']);
$staffOptions = $repository->activeStaff();
$planIds = array_map(static fn (array $plan): int => (int) $plan['id'], $plans);
$staffIds = array_map(static fn (array $staff): int => (int) $staff['id'], $staffOptions);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/student-edit.php?id=' . $studentId);
    }

    $result = $formService->validate($_POST, $planIds, $staffIds, true);

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the student form.');
        redirect('admin/student-edit.php?id=' . $studentId);
    }

    $repository->update($studentId, $result['data']);
    flash('success', 'Student updated.');
    redirect('admin/students.php');
}

render('admin/student_form', [
    'title' => 'Edit Student',
    'pageTitle' => 'Edit Student',
    'formAction' => app_url('admin/student-edit.php?id=' . $studentId),
    'submitLabel' => 'Save Student',
    'studentsBackLink' => app_url('admin/students.php'),
    'values' => $formService->defaults($student),
    'plans' => $plans,
    'staffOptions' => $staffOptions,
    'statuses' => \App\Services\StudentFormService::STATUSES,
    'allowStaffAssignment' => true,
], 'admin');

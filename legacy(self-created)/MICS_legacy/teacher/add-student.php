<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\StudentRepository;
use App\Services\StudentFormService;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireTeacher();

$staffId = (int) (Auth::user()['staff_id'] ?? 0);
$repository = new StudentRepository;
$formService = new StudentFormService;
$plans = $repository->activePlans();
$planIds = array_map(static fn (array $plan): int => (int) $plan['id'], $plans);
$staffIds = [$staffId];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('teacher/add-student.php');
    }

    $result = $formService->validate($_POST, $planIds, $staffIds, false, $staffId);

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the student form.');
        redirect('teacher/add-student.php');
    }

    $repository->create($result['data']);
    flash('success', 'Student created.');
    redirect('teacher/students.php');
}

render('teacher/student_form', [
    'title' => 'Add Student',
    'pageTitle' => 'Add Student',
    'formAction' => app_url('teacher/add-student.php'),
    'submitLabel' => 'Create Student',
    'studentsBackLink' => app_url('teacher/students.php'),
    'values' => $formService->defaults(),
    'plans' => $plans,
    'statuses' => StudentFormService::STATUSES,
], 'teacher');

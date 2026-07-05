<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\StudentRepository;
use App\Services\StudentFormService;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireTeacher();

$staffId = (int) (Auth::user()['staff_id'] ?? 0);
$studentId = (int) ($_GET['id'] ?? 0);
$repository = new StudentRepository;
$student = $repository->findByIdForTeacher($studentId, $staffId);

if ($student === null) {
    flash('error', 'Student not found.');
    redirect('teacher/students.php');
}

$formService = new StudentFormService;
$plans = $repository->activePlansIncluding((int) $student['plan_id']);
$planIds = array_map(static fn (array $plan): int => (int) $plan['id'], $plans);
$staffIds = [$staffId];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('teacher/student-edit.php?id='.$studentId);
    }

    $result = $formService->validate($_POST, $planIds, $staffIds, false, $staffId);

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the student form.');
        redirect('teacher/student-edit.php?id='.$studentId);
    }

    $repository->update($studentId, $result['data']);
    flash('success', 'Student updated.');
    redirect('teacher/students.php');
}

render('teacher/student_form', [
    'title' => 'Edit Student',
    'pageTitle' => 'Edit Student',
    'formAction' => app_url('teacher/student-edit.php?id='.$studentId),
    'submitLabel' => 'Save Student',
    'studentsBackLink' => app_url('teacher/students.php'),
    'values' => $formService->defaults($student),
    'plans' => $plans,
    'statuses' => StudentFormService::STATUSES,
], 'teacher');

<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\StudentRepository;
use App\Services\StudentFormService;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireTeacher();

$staffId = (int) (Auth::user()['staff_id'] ?? 0);
$repository = new StudentRepository;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('teacher/students.php');
    }

    $studentId = (int) ($_POST['student_id'] ?? 0);
    $status = trim((string) ($_POST['status'] ?? ''));
    $student = $repository->findByIdForTeacher($studentId, $staffId);

    if ($student === null || ! in_array($status, StudentFormService::STATUSES, true)) {
        flash('error', 'Unable to update that student status.');
        redirect('teacher/students.php');
    }

    $repository->update($studentId, [
        'first_name' => $student['first_name'],
        'family_name' => $student['family_name'],
        'father_name' => $student['father_name'],
        'phone' => $student['phone'],
        'email' => $student['email'],
        'status' => $status,
        'plan_id' => (int) $student['plan_id'],
        'staff_id' => $staffId,
        'discount_amount' => (float) $student['discount_amount'],
        'joined_at' => $student['joined_at'],
        'comments' => $student['comments'],
    ]);

    flash('success', 'Student status updated.');
    redirect('teacher/students.php');
}

$search = trim((string) ($_GET['q'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$students = $repository->findForTeacherList($staffId, $search !== '' ? $search : null, $status !== '' ? $status : null);

render('teacher/students', [
    'title' => 'My Students',
    'students' => $students,
    'search' => $search,
    'status' => $status,
    'statuses' => StudentFormService::STATUSES,
], 'teacher');

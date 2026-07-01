<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

\App\Auth::requireAdmin();

$repository = new \App\Repositories\StudentRepository();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/students.php');
    }

    $studentId = (int) ($_POST['student_id'] ?? 0);
    $status = trim((string) ($_POST['status'] ?? ''));
    $student = $repository->findByIdForAdmin($studentId);

    if ($student === null || ! in_array($status, \App\Services\StudentFormService::STATUSES, true)) {
        flash('error', 'Unable to update that student status.');
        redirect('admin/students.php');
    }

    $repository->update($studentId, [
        'first_name' => $student['first_name'],
        'family_name' => $student['family_name'],
        'father_name' => $student['father_name'],
        'phone' => $student['phone'],
        'email' => $student['email'],
        'status' => $status,
        'plan_id' => (int) $student['plan_id'],
        'staff_id' => (int) $student['staff_id'],
        'discount_amount' => (float) $student['discount_amount'],
        'joined_at' => $student['joined_at'],
        'comments' => $student['comments'],
    ]);

    flash('success', 'Student status updated.');
    redirect('admin/students.php');
}

$search = trim((string) ($_GET['q'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$students = $repository->findForAdminList($search !== '' ? $search : null, $status !== '' ? $status : null);

render('admin/students', [
    'title' => 'Students',
    'students' => $students,
    'search' => $search,
    'status' => $status,
    'statuses' => \App\Services\StudentFormService::STATUSES,
], 'admin');

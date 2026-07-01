<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

\App\Auth::requireAdmin();

$repository = new \App\Repositories\StaffRepository();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/staff.php');
    }

    $staffId = (int) ($_POST['staff_id'] ?? 0);
    $status = trim((string) ($_POST['status'] ?? ''));
    $staff = $repository->findById($staffId);

    if ($staff === null || ! in_array($status, \App\Services\StaffFormService::STATUSES, true)) {
        flash('error', 'Unable to update that staff status.');
        redirect('admin/staff.php');
    }

    $repository->update($staffId, [
        'role' => $staff['role'],
        'first_name' => $staff['first_name'],
        'family_name' => $staff['family_name'],
        'father_name' => $staff['father_name'],
        'status' => $status,
        'payout_card_number' => $staff['payout_card_number'],
        'fixed_salary_amount' => $staff['fixed_salary_amount'],
        'phone' => $staff['phone'],
        'email' => $staff['email'],
        'comments' => $staff['comments'],
    ]);

    flash('success', 'Staff status updated.');
    redirect('admin/staff.php');
}

$search = trim((string) ($_GET['q'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$staff = $repository->findForList($search !== '' ? $search : null, $status !== '' ? $status : null);

render('admin/staff', [
    'title' => 'Staff',
    'staff' => $staff,
    'search' => $search,
    'status' => $status,
    'statuses' => \App\Services\StaffFormService::STATUSES,
], 'admin');

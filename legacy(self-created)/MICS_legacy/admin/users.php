<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\UserRepository;
use App\Services\UserFormService;

require dirname(__DIR__).'/app/bootstrap.php';
Auth::requireAdmin();

$repository = new UserRepository;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/users.php');
    }

    $userId = (int) ($_POST['user_id'] ?? 0);
    $isActive = (string) ($_POST['is_active'] ?? '');
    $user = $repository->findById($userId);

    if ($user === null || ! in_array($isActive, UserFormService::ACTIVE_OPTIONS, true)) {
        flash('error', 'Unable to update that user status.');
        redirect('admin/users.php');
    }

    $repository->update($userId, [
        'staff_id' => (int) $user['staff_id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'is_active' => $isActive === '1',
    ]);

    flash('success', 'User access updated.');
    redirect('admin/users.php');
}

$search = trim((string) ($_GET['q'] ?? ''));
$role = trim((string) ($_GET['role'] ?? ''));
$isActive = trim((string) ($_GET['is_active'] ?? ''));
$staffId = (int) ($_GET['staff_id'] ?? 0);
$users = $repository->findForList(
    $search !== '' ? $search : null,
    $role !== '' ? $role : null,
    $isActive !== '' ? $isActive : null,
    $staffId > 0 ? $staffId : null
);
$staffOptions = $repository->staffOptions();

render('admin/users', [
    'title' => 'Users',
    'users' => $users,
    'search' => $search,
    'role' => $role,
    'isActive' => $isActive,
    'staffId' => $staffId > 0 ? (string) $staffId : '',
    'roles' => UserFormService::ROLES,
    'staffOptions' => $staffOptions,
], 'admin');

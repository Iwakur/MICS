<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\UserRepository;
use App\Services\UserFormService;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireAdmin();

$userId = (int) ($_GET['id'] ?? 0);
$repository = new UserRepository;
$user = $repository->findById($userId);

if ($user === null) {
    flash('error', 'User not found.');
    redirect('admin/users.php');
}

$formService = new UserFormService;
$staffOptions = $repository->staffOptions();
$staffIds = array_map(static fn (array $staff): int => (int) $staff['id'], $staffOptions);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/user-edit.php?id='.$userId);
    }

    $result = $formService->validate(
        $_POST,
        $staffIds,
        $repository->usernameExists(trim((string) ($_POST['username'] ?? '')), $userId)
    );

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the user form.');
        redirect('admin/user-edit.php?id='.$userId);
    }

    $repository->update($userId, $result['data']);
    flash('success', 'User account updated.');
    redirect('admin/users.php');
}

render('admin/user_form', [
    'title' => 'Edit User',
    'pageTitle' => 'Edit User',
    'formAction' => app_url('admin/user-edit.php?id='.$userId),
    'submitLabel' => 'Save User',
    'usersBackLink' => app_url('admin/users.php'),
    'values' => $formService->defaults($user),
    'staffOptions' => $staffOptions,
    'roles' => UserFormService::ROLES,
    'defaultPassword' => default_user_password(),
    'showPasswordNotice' => false,
], 'admin');

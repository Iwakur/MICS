<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\UserRepository;
use App\Services\UserFormService;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireAdmin();

$repository = new UserRepository;
$formService = new UserFormService;
$staffOptions = $repository->staffOptions();
$staffIds = array_map(static fn (array $staff): int => (int) $staff['id'], $staffOptions);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/user-create.php');
    }

    $result = $formService->validate($_POST, $staffIds, $repository->usernameExists(trim((string) ($_POST['username'] ?? ''))));

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the user form.');
        redirect('admin/user-create.php');
    }

    $repository->create($result['data'], default_user_password());
    flash('success', 'User account created with the configured starter password.');
    redirect('admin/users.php');
}

render('admin/user_form', [
    'title' => 'Create User',
    'pageTitle' => 'Create User',
    'formAction' => app_url('admin/user-create.php'),
    'submitLabel' => 'Create User',
    'usersBackLink' => app_url('admin/users.php'),
    'values' => $formService->defaults(),
    'staffOptions' => $staffOptions,
    'roles' => UserFormService::ROLES,
    'defaultPassword' => default_user_password(),
    'showPasswordNotice' => true,
], 'admin');

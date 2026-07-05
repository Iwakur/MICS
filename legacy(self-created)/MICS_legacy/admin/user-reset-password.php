<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\UserRepository;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireAdmin();

$userId = (int) ($_GET['id'] ?? 0);
$repository = new UserRepository;
$user = $repository->findWithStaffById($userId);

if ($user === null) {
    flash('error', 'User not found.');
    redirect('admin/users.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/user-reset-password.php?id='.$userId);
    }

    $repository->resetPassword($userId, default_user_password());
    flash('success', 'Password reset to the configured starter password.');
    redirect('admin/users.php');
}

render('admin/user_reset_password', [
    'title' => 'Reset User Password',
    'pageTitle' => 'Reset User Password',
    'user' => $user,
    'usersBackLink' => app_url('admin/users.php'),
    'defaultPassword' => default_user_password(),
], 'admin');

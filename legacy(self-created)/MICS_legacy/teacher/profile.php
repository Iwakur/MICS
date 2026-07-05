<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\TeacherProfileRepository;
use App\Services\TeacherPasswordService;

require dirname(__DIR__).'/app/bootstrap.php';
Auth::requireTeacher();

$userId = (int) (Auth::user()['id'] ?? 0);
$repository = new TeacherProfileRepository;
$passwordService = new TeacherPasswordService;
$profile = $repository->findByUserId($userId);

if ($profile === null) {
    Auth::logout();
    flash('error', 'Teacher profile could not be loaded.');
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('teacher/profile.php');
    }

    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $currentPasswordValid = $currentPassword !== '' && $repository->verifyCurrentPassword($userId, $currentPassword);
    $result = $passwordService->validateChange($_POST, $currentPasswordValid);

    if ($result['errors'] !== []) {
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the password form.');
        redirect('teacher/profile.php');
    }

    $repository->updatePassword($userId, $result['data']['new_password']);
    flash('success', 'Password changed successfully.');
    redirect('teacher/profile.php');
}

render('teacher/profile', [
    'title' => 'Profile',
    'profile' => $profile,
], 'teacher');

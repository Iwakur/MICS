<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

\App\Auth::requireGuest();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('login.php');
    }

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    try {
        if (\App\Auth::attempt($username, $password)) {
            redirect(\App\Auth::isAdmin() ? 'admin/dashboard.php' : 'teacher/dashboard.php');
        }

        flash('error', 'Invalid username or password.');
    } catch (Throwable $throwable) {
        flash('error', 'Database is not ready. Check PostgreSQL setup and the pdo_pgsql PHP extension.');
    }

    redirect('login.php');
}

render('auth/login', [
    'title' => 'Login',
], 'guest');

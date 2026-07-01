<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

if (! \App\Auth::check()) {
    redirect('login.php');
}

redirect(\App\Auth::isAdmin() ? 'admin/dashboard.php' : 'teacher/dashboard.php');

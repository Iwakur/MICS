<?php

declare(strict_types=1);
use App\Auth;

require __DIR__.'/app/bootstrap.php';

if (! Auth::check()) {
    redirect('login.php');
}

redirect(Auth::isAdmin() ? 'admin/dashboard.php' : 'teacher/dashboard.php');

<?php

declare(strict_types=1);
use App\Auth;

require __DIR__.'/app/bootstrap.php';

Auth::logout();
flash('error', 'You have been logged out.');
redirect('login.php');

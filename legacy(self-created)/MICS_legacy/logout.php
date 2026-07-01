<?php

declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

\App\Auth::logout();
flash('error', 'You have been logged out.');
redirect('login.php');

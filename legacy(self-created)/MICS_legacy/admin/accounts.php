<?php

declare(strict_types=1);
use App\Auth;

require dirname(__DIR__).'/app/bootstrap.php';
Auth::requireAdmin();

render('admin/placeholder', [
    'title' => 'Accounts',
    'pageTitle' => 'Accounts',
    'pageDescription' => 'Chart of accounts management area.',
    'pageNote' => 'Keep accounts as accounting buckets. Detailed business labels should usually stay in categories or descriptions.',
], 'admin');

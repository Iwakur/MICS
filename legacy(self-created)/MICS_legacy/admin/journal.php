<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
\App\Auth::requireAdmin();

render('admin/placeholder', [
    'title' => 'Journal',
    'pageTitle' => 'Journal Entries',
    'pageDescription' => 'Audit and accounting posting inspection area.',
    'pageNote' => 'Final finance workflows should route through balanced journal posting inside one database transaction.',
], 'admin');

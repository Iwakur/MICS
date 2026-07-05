<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\SqlConsoleRepository;
use App\Services\SqlConsoleService;

require dirname(__DIR__).'/app/bootstrap.php';
Auth::requireAdmin();

$service = new SqlConsoleService(new SqlConsoleRepository);
$sql = '';
$errors = [];
$result = null;
$executionError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/settings.php');
    }

    $validation = $service->validate($_POST);
    $sql = $validation['sql'];
    $errors = $validation['errors'];

    if ($errors === []) {
        try {
            $result = $service->execute($sql);
        } catch (Throwable $throwable) {
            $executionError = $throwable->getMessage();
        }
    }
}

render('admin/settings', [
    'title' => 'SQL Console',
    'sql' => $sql,
    'errors' => $errors,
    'result' => $result,
    'executionError' => $executionError,
], 'admin');

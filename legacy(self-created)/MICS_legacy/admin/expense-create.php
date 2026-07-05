<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\ExpenseRepository;
use App\Services\ExpenseFormService;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireAdmin();

$repository = new ExpenseRepository;
$formService = new ExpenseFormService;
$categories = $repository->categories();
$accounts = $repository->paidFromAccounts();
$staff = $repository->activeStaff();
$categoryIds = array_map(static fn (array $row): int => (int) $row['id'], $categories);
$accountIds = array_map(static fn (array $row): int => (int) $row['id'], $accounts);
$staffIds = array_map(static fn (array $row): int => (int) $row['id'], $staff);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/expense-create.php');
    }

    $result = $formService->validate($_POST, $categoryIds, $accountIds, $staffIds);

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the expense form.');
        redirect('admin/expense-create.php');
    }

    $repository->create($result['data']);
    flash('success', 'Expense created.');
    redirect('admin/expenses.php');
}

render('admin/expense_form', [
    'title' => 'Create Expense',
    'pageTitle' => 'Create Expense',
    'formAction' => app_url('admin/expense-create.php'),
    'submitLabel' => 'Create Expense',
    'backLink' => app_url('admin/expenses.php'),
    'values' => $formService->defaults(),
    'categories' => $categories,
    'accounts' => $accounts,
    'staff' => $staff,
    'statuses' => ExpenseFormService::STATUSES,
], 'admin');

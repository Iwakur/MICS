<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\ExpenseRepository;
use App\Services\ExpenseFormService;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireAdmin();

$expenseId = (int) ($_GET['id'] ?? 0);
$repository = new ExpenseRepository;
$expense = $repository->findById($expenseId);

if ($expense === null) {
    flash('error', 'Expense not found.');
    redirect('admin/expenses.php');
}

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
        redirect('admin/expense-edit.php?id='.$expenseId);
    }

    $result = $formService->validate($_POST, $categoryIds, $accountIds, $staffIds);

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the expense form.');
        redirect('admin/expense-edit.php?id='.$expenseId);
    }

    $result['data']['import_row_id'] = $expense['import_row_id'] !== null ? (int) $expense['import_row_id'] : null;
    $repository->update($expenseId, $result['data']);
    flash('success', 'Expense updated.');
    redirect('admin/expenses.php');
}

render('admin/expense_form', [
    'title' => 'Edit Expense',
    'pageTitle' => 'Edit Expense',
    'formAction' => app_url('admin/expense-edit.php?id='.$expenseId),
    'submitLabel' => 'Save Expense',
    'backLink' => app_url('admin/expenses.php'),
    'values' => $formService->defaults($expense),
    'categories' => $categories,
    'accounts' => $accounts,
    'staff' => $staff,
    'statuses' => ExpenseFormService::STATUSES,
], 'admin');

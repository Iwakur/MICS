<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\ExpenseRepository;
use App\Services\ExpenseFormService;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireAdmin();

$repository = new ExpenseRepository;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/expenses.php');
    }

    $action = trim((string) ($_POST['action'] ?? ''));
    $expenseId = (int) ($_POST['expense_id'] ?? 0);
    $expense = $repository->findById($expenseId);

    if ($action === 'post_expense') {
        if ($expense === null || ($expense['status'] ?? null) !== 'draft') {
            flash('error', 'Only draft expenses can be posted.');
            redirect('admin/expenses.php');
        }

        $repository->post($expenseId);
        flash('success', 'Expense posted.');
        redirect('admin/expenses.php');
    }

    if ($action === 'void_expense') {
        if ($expense === null || ($expense['status'] ?? null) === 'void') {
            flash('error', 'That expense cannot be voided.');
            redirect('admin/expenses.php');
        }

        $repository->void($expenseId);
        flash('success', 'Expense voided.');
        redirect('admin/expenses.php');
    }

    flash('error', 'Unsupported expense action.');
    redirect('admin/expenses.php');
}

$status = trim((string) ($_GET['status'] ?? ''));
$categoryId = (int) ($_GET['category_id'] ?? 0);
$expenses = $repository->list($status !== '' ? $status : null, $categoryId > 0 ? $categoryId : null);
$categories = $repository->categories();

render('admin/expenses', [
    'title' => 'Expenses',
    'expenses' => $expenses,
    'categories' => $categories,
    'status' => $status,
    'categoryId' => $categoryId > 0 ? (string) $categoryId : '',
    'statuses' => ExpenseFormService::STATUSES,
], 'admin');

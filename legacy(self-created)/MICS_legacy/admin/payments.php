<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\PaymentRepository;
use App\Services\PaymentFormService;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireAdmin();

$repository = new PaymentRepository;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/payments.php');
    }

    $action = trim((string) ($_POST['action'] ?? ''));
    $paymentId = (int) ($_POST['payment_id'] ?? 0);
    $payment = $repository->findById($paymentId);

    if ($action === 'confirm_payment') {
        if ($payment === null || ($payment['status'] ?? null) !== 'pending') {
            flash('error', 'Only pending payments can be confirmed.');
            redirect('admin/payments.php');
        }

        $repository->confirm($paymentId);
        flash('success', 'Payment confirmed.');
        redirect('admin/payments.php');
    }

    if ($action === 'void_payment') {
        if ($payment === null || ($payment['status'] ?? null) === 'void') {
            flash('error', 'That payment cannot be voided.');
            redirect('admin/payments.php');
        }

        $repository->void($paymentId);
        flash('success', 'Payment voided.');
        redirect('admin/payments.php');
    }

    flash('error', 'Unsupported payment action.');
    redirect('admin/payments.php');
}

$status = trim((string) ($_GET['status'] ?? ''));
$studentId = (int) ($_GET['student_id'] ?? 0);
$payments = $repository->list($status !== '' ? $status : null, $studentId > 0 ? $studentId : null);
$students = $repository->studentDirectory();

render('admin/payments', [
    'title' => 'Payments',
    'payments' => $payments,
    'students' => $students,
    'status' => $status,
    'studentId' => $studentId > 0 ? (string) $studentId : '',
    'statuses' => PaymentFormService::STATUSES,
], 'admin');

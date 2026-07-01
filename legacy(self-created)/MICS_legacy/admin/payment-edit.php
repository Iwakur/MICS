<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

\App\Auth::requireAdmin();

$paymentId = (int) ($_GET['id'] ?? 0);
$repository = new \App\Repositories\PaymentRepository();
$payment = $repository->findById($paymentId);

if ($payment === null) {
    flash('error', 'Payment not found.');
    redirect('admin/payments.php');
}

$formService = new \App\Services\PaymentFormService();
$students = $repository->studentDirectory();
$studentIds = array_map(static fn (array $student): int => (int) $student['id'], $students);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/payment-edit.php?id=' . $paymentId);
    }

    $result = $formService->validate($_POST, $studentIds);

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the payment form.');
        redirect('admin/payment-edit.php?id=' . $paymentId);
    }

    $result['data']['import_row_id'] = $payment['import_row_id'] !== null ? (int) $payment['import_row_id'] : null;
    $repository->update($paymentId, $result['data']);
    flash('success', 'Payment updated.');
    redirect('admin/payments.php');
}

render('admin/payment_form', [
    'title' => 'Edit Payment',
    'pageTitle' => 'Edit Payment',
    'formAction' => app_url('admin/payment-edit.php?id=' . $paymentId),
    'submitLabel' => 'Save Payment',
    'backLink' => app_url('admin/payments.php'),
    'values' => $formService->defaults($payment),
    'students' => $students,
    'statuses' => \App\Services\PaymentFormService::STATUSES,
], 'admin');

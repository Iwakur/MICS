<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\PaymentRepository;
use App\Services\PaymentFormService;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireAdmin();

$repository = new PaymentRepository;
$formService = new PaymentFormService;
$students = $repository->studentDirectory();
$studentIds = array_map(static fn (array $student): int => (int) $student['id'], $students);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/payment-create.php');
    }

    $result = $formService->validate($_POST, $studentIds);

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the payment form.');
        redirect('admin/payment-create.php');
    }

    $repository->create($result['data']);
    flash('success', 'Payment created.');
    redirect('admin/payments.php');
}

render('admin/payment_form', [
    'title' => 'Create Payment',
    'pageTitle' => 'Create Payment',
    'formAction' => app_url('admin/payment-create.php'),
    'submitLabel' => 'Create Payment',
    'backLink' => app_url('admin/payments.php'),
    'values' => $formService->defaults(),
    'students' => $students,
    'statuses' => PaymentFormService::STATUSES,
], 'admin');

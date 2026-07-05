<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\PayoutRepository;
use App\Services\PayoutFormService;

require dirname(__DIR__).'/app/bootstrap.php';

Auth::requireAdmin();

$payoutId = (int) ($_GET['id'] ?? 0);
$repository = new PayoutRepository;
$payout = $repository->findPayoutById($payoutId);

if ($payout === null) {
    flash('error', 'Payout not found.');
    redirect('admin/payouts.php');
}

if (($payout['status'] ?? null) !== 'draft') {
    flash('error', 'Only draft payouts can be edited.');
    redirect('admin/payouts.php');
}

$formService = new PayoutFormService;
$staff = $repository->activeStaff();
$staffIds = array_map(static fn (array $row): int => (int) $row['id'], $staff);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/payout-edit.php?id='.$payoutId);
    }

    $result = $formService->validate($_POST, $staffIds);

    if ($result['errors'] !== []) {
        flash('_old_input', $_POST);
        flash('_form_errors', $result['errors']);
        flash('error', 'Please correct the payout form.');
        redirect('admin/payout-edit.php?id='.$payoutId);
    }

    $repository->updateDraft($payoutId, $result['data']);
    flash('success', 'Payout updated.');
    redirect('admin/payouts.php');
}

render('admin/payout_form', [
    'title' => 'Edit Payout',
    'pageTitle' => 'Edit Payout',
    'formAction' => app_url('admin/payout-edit.php?id='.$payoutId),
    'submitLabel' => 'Save Payout',
    'backLink' => app_url('admin/payouts.php'),
    'values' => $formService->defaults($payout),
    'staff' => $staff,
], 'admin');

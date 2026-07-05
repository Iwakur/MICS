<?php

declare(strict_types=1);
use App\Auth;
use App\Repositories\PayoutRepository;
use App\Services\PayoutService;

require dirname(__DIR__).'/app/bootstrap.php';
Auth::requireAdmin();

$service = new PayoutService;
$repository = new PayoutRepository;
$period = $service->currentMonthPeriod();
$monthStart = $service->asSqlTimestamp($period['month_start']);
$nextMonthStart = $service->asSqlTimestamp($period['next_month_start']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/payouts.php');
    }

    $action = trim((string) ($_POST['action'] ?? ''));

    if ($action === 'create_draft') {
        $staffId = (int) ($_POST['staff_id'] ?? 0);
        $suggestions = $repository->adminSuggestions($nextMonthStart);
        $staffSuggestion = null;

        foreach ($suggestions as $suggestion) {
            if ((int) $suggestion['id'] === $staffId) {
                $staffSuggestion = $suggestion;
                break;
            }
        }

        $validation = $service->validateDraftCreation(
            $staffSuggestion,
            $staffId > 0 && $repository->monthRecordExists($staffId, $monthStart, $nextMonthStart)
        );

        if (! $validation['valid']) {
            flash('error', (string) $validation['message']);
            redirect('admin/payouts.php');
        }

        $repository->createDraft(
            $staffId,
            (float) $staffSuggestion['suggested_amount'],
            $service->asSqlTimestamp(current_app_datetime()),
            strtolower((string) ($staffSuggestion['role'] ?? '')) === 'teacher'
                ? 'Calculated from active students and plan teacher shares for '.$period['label'].'.'
                : 'Fixed salary draft for '.$period['label'].'.'
        );

        flash('success', 'Draft payout created.');
        redirect('admin/payouts.php');
    }

    if ($action === 'post_payout') {
        $payoutId = (int) ($_POST['payout_id'] ?? 0);
        $payout = $repository->findPayoutById($payoutId);
        $validation = $service->validatePostTransition($payout);

        if (! $validation['valid']) {
            flash('error', (string) $validation['message']);
            redirect('admin/payouts.php');
        }

        $repository->postDraft($payoutId);
        flash('success', 'Payout posted.');
        redirect('admin/payouts.php');
    }

    if ($action === 'void_payout') {
        $payoutId = (int) ($_POST['payout_id'] ?? 0);
        $payout = $repository->findPayoutById($payoutId);
        $validation = $service->validateVoidTransition($payout);

        if (! $validation['valid']) {
            flash('error', (string) $validation['message']);
            redirect('admin/payouts.php');
        }

        $repository->voidPayout($payoutId);
        flash('success', 'Payout voided.');
        redirect('admin/payouts.php');
    }

    flash('error', 'Unsupported payout action.');
    redirect('admin/payouts.php');
}

$status = trim((string) ($_GET['status'] ?? ''));
$staffId = (int) ($_GET['staff_id'] ?? 0);
$suggestions = $repository->adminSuggestions($nextMonthStart);
$history = $repository->payoutHistory($status !== '' ? $status : null, $staffId > 0 ? $staffId : null);
$staff = $repository->activeStaff();

render('admin/payouts', [
    'title' => 'Staff Payouts',
    'periodLabel' => $period['label'],
    'suggestions' => $suggestions,
    'history' => $history,
    'staff' => $staff,
    'status' => $status,
    'staffId' => $staffId > 0 ? (string) $staffId : '',
    'statuses' => ['draft', 'posted', 'void'],
], 'admin');

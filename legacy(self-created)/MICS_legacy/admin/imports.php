<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

\App\Auth::requireAdmin();

$repository = new \App\Repositories\PaymentImportRepository();
$service = new \App\Services\PaymentImportService();
$authUser = \App\Auth::user();
$allowedStatuses = \App\Services\PaymentImportService::ROW_STATUSES;

$buildRedirect = static function (?int $batchId, string $status): string {
    $query = [];

    if ($batchId !== null && $batchId > 0) {
        $query['batch_id'] = (string) $batchId;
    }

    if ($status !== '') {
        $query['status'] = $status;
    }

    return 'admin/imports.php' . ($query === [] ? '' : '?' . http_build_query($query));
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (! verify_csrf($_POST['_csrf'] ?? null)) {
        flash('error', 'Invalid form token.');
        redirect('admin/imports.php');
    }

    $action = trim((string) ($_POST['action'] ?? ''));
    $batchFilter = (int) ($_POST['batch_filter'] ?? 0);
    $batchIdForRedirect = $batchFilter > 0 ? $batchFilter : null;
    $statusFilter = trim((string) ($_POST['status_filter'] ?? ''));

    try {
        if ($action === 'upload_statement') {
            $result = $service->importUploadedStatement($_FILES['statement_file'] ?? [], (int) ($authUser['id'] ?? 0));
            flash('success', $result['message']);
            redirect($buildRedirect((int) $result['batch_id'], ''));
        }

        if ($action === 'create_payment_draft') {
            $rowId = (int) ($_POST['row_id'] ?? 0);
            $studentId = (int) ($_POST['student_id'] ?? 0);
            $coveredMonth = $service->normalizeCoveredMonth((string) ($_POST['covered_month'] ?? ''));

            if ($studentId <= 0) {
                throw new InvalidArgumentException('Choose the student before creating the payment draft.');
            }

            $repository->createPaymentDraftFromRow($rowId, $studentId, $coveredMonth, (int) ($authUser['id'] ?? 0));
            flash('success', 'Payment draft created from the imported row.');
            redirect($buildRedirect($batchIdForRedirect, $statusFilter));
        }

        if ($action === 'create_expense_draft') {
            $rowId = (int) ($_POST['row_id'] ?? 0);
            $categoryId = (int) ($_POST['category_id'] ?? 0);
            $paidFromAccountId = (int) ($_POST['paid_from_account_id'] ?? 0);

            if ($categoryId <= 0 || $paidFromAccountId <= 0) {
                throw new InvalidArgumentException('Choose both expense category and paying account.');
            }

            $repository->createExpenseDraftFromRow($rowId, $categoryId, $paidFromAccountId, (int) ($authUser['id'] ?? 0));
            flash('success', 'Expense draft created from the imported row.');
            redirect($buildRedirect($batchIdForRedirect, $statusFilter));
        }

        if ($action === 'create_payout_draft') {
            $rowId = (int) ($_POST['row_id'] ?? 0);
            $staffId = (int) ($_POST['staff_id'] ?? 0);

            if ($staffId <= 0) {
                throw new InvalidArgumentException('Choose the staff member before creating the payout draft.');
            }

            $repository->createPayoutDraftFromRow($rowId, $staffId, (int) ($authUser['id'] ?? 0));
            flash('success', 'Payout draft created from the imported row.');
            redirect($buildRedirect($batchIdForRedirect, $statusFilter));
        }

        if (in_array($action, ['ignore_row', 'delete_row', 'restore_row'], true)) {
            $rowId = (int) ($_POST['row_id'] ?? 0);
            $status = match ($action) {
                'ignore_row' => 'ignored',
                'delete_row' => 'deleted',
                default => 'new',
            };

            $repository->updateRowStatus($rowId, $status, (int) ($authUser['id'] ?? 0));
            flash('success', 'Row review status updated.');
            redirect($buildRedirect($batchIdForRedirect, $statusFilter));
        }

        flash('error', 'Unsupported import action.');
        redirect($buildRedirect($batchIdForRedirect, $statusFilter));
    } catch (Throwable $throwable) {
        flash('error', $throwable->getMessage());
        redirect($buildRedirect($batchIdForRedirect, $statusFilter));
    }
}

$batchId = (int) ($_GET['batch_id'] ?? 0);
$batchId = $batchId > 0 ? $batchId : null;
$status = trim((string) ($_GET['status'] ?? ''));

if ($status !== '' && ! in_array($status, $allowedStatuses, true)) {
    $status = '';
}

$batches = $repository->batchSummaries();
$studentDirectory = $repository->studentDirectory();
$expenseCategories = $repository->expenseCategories();
$paidFromAccounts = $repository->paidFromAccounts();
$staffDirectory = $repository->activeStaff();

$studentsById = [];
foreach ($studentDirectory as $student) {
    $studentName = person_name_from_row($student);
    $teacherName = person_name_from_row($student, 'staff_');

    $studentsById[(int) $student['id']] = [
        'id' => (int) $student['id'],
        'label' => trim($studentName) . ' | ' . ((string) ($student['plan_name'] ?? 'No plan')),
        'meta' => trim($teacherName) !== '' ? $teacherName : 'No teacher',
        'status' => (string) ($student['status'] ?? ''),
    ];
}

$staffById = [];
foreach ($staffDirectory as $staff) {
    $staffName = person_name_from_row($staff);

    $staffById[(int) $staff['id']] = [
        'id' => (int) $staff['id'],
        'label' => $staffName,
        'meta' => (string) $staff['role'],
    ];
}

$rows = [];
foreach ($repository->listRows($batchId, $status !== '' ? $status : null) as $row) {
    $suggestedIds = $service->decodeSuggestedStudentIds((string) ($row['suggested_student_ids'] ?? ''));
    $suggestedStudents = [];

    foreach ($suggestedIds as $suggestedId) {
        if (isset($studentsById[$suggestedId])) {
            $suggestedStudents[] = $studentsById[$suggestedId];
        }
    }

    $matchedName = person_name_from_row($row, 'matched_');
    $payoutStaffName = person_name_from_row($row, 'payout_staff_');

    $row['suggested_students'] = $suggestedStudents;
    $row['matched_student_name'] = $matchedName !== '' ? $matchedName : null;
    $row['default_student_id'] = $suggestedIds[0] ?? ($row['matched_student_id'] !== null ? (int) $row['matched_student_id'] : 0);
    $row['created_document_label'] = match ((string) ($row['created_document_type'] ?? '')) {
        'payment' => 'Payment #' . (string) $row['payment_id'],
        'expense' => 'Expense #' . (string) $row['expense_id'] . ((string) ($row['expense_category_name'] ?? '') !== '' ? ' | ' . $row['expense_category_name'] : ''),
        'payout' => 'Payout #' . (string) $row['payout_id'] . ($payoutStaffName !== '' ? ' | ' . $payoutStaffName : ''),
        default => null,
    };
    $rows[] = $row;
}

render('admin/imports', [
    'title' => 'Import Hub',
    'pageTitle' => 'Import Hub',
    'pageDescription' => 'Upload bank statements, review raw rows, and classify them into finance drafts.',
    'pageNote' => 'One imported row can create one payment, expense, or payout draft. Raw import history stays separate from business records.',
    'batches' => $batches,
    'rows' => $rows,
    'batchId' => $batchId,
    'status' => $status,
    'statuses' => $allowedStatuses,
    'studentsById' => $studentsById,
    'staffById' => $staffById,
    'expenseCategories' => $expenseCategories,
    'paidFromAccounts' => $paidFromAccounts,
    'statusLabel' => static fn (string $value): string => $service->statusLabel($value),
], 'admin');

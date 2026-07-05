<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;
use Throwable;

final class PaymentImportRepository
{
    public function findBatchByFileHash(string $fileHash): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, original_filename, total_rows, new_rows, duplicate_rows, created_at
             FROM statement_import_batches
             WHERE file_hash = :file_hash
             LIMIT 1'
        );

        $statement->execute(['file_hash' => $fileHash]);
        $batch = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($batch) ? $batch : null;
    }

    public function createBatchWithRows(array $batchData, array $rows): array
    {
        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();

            $batchStatement = $pdo->prepare(
                'INSERT INTO statement_import_batches (
                    original_filename,
                    stored_filename,
                    file_hash,
                    account_number,
                    currency,
                    imported_by_user_id,
                    total_rows,
                    new_rows,
                    duplicate_rows,
                    created_at
                 ) VALUES (
                    :original_filename,
                    :stored_filename,
                    :file_hash,
                    :account_number,
                    :currency,
                    :imported_by_user_id,
                    :total_rows,
                    :new_rows,
                    :duplicate_rows,
                    CURRENT_TIMESTAMP
                 )
                 RETURNING id'
            );

            $batchStatement->execute($batchData);
            $batchId = (int) $batchStatement->fetchColumn();

            $rowStatement = $pdo->prepare(
                'INSERT INTO statement_import_rows (
                    batch_id,
                    row_number,
                    row_hash,
                    owner_edrpou,
                    owner_mfo,
                    account_number,
                    currency,
                    document_number,
                    operation_date,
                    bank_mfo,
                    bank_name,
                    correspondent_account,
                    correspondent_edrpou,
                    correspondent_name,
                    amount,
                    payment_purpose,
                    direction,
                    status,
                    raw_payload,
                    suggested_student_ids,
                    created_at,
                    updated_at
                 ) VALUES (
                    :batch_id,
                    :row_number,
                    :row_hash,
                    :owner_edrpou,
                    :owner_mfo,
                    :account_number,
                    :currency,
                    :document_number,
                    :operation_date,
                    :bank_mfo,
                    :bank_name,
                    :correspondent_account,
                    :correspondent_edrpou,
                    :correspondent_name,
                    :amount,
                    :payment_purpose,
                    :direction,
                    :status,
                    CAST(:raw_payload AS jsonb),
                    CAST(:suggested_student_ids AS jsonb),
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP
                 )
                 ON CONFLICT (row_hash) DO NOTHING
                 RETURNING id'
            );

            $newRows = 0;

            foreach ($rows as $row) {
                $rowStatement->execute([
                    'batch_id' => $batchId,
                    'row_number' => $row['row_number'],
                    'row_hash' => $row['row_hash'],
                    'owner_edrpou' => $row['owner_edrpou'],
                    'owner_mfo' => $row['owner_mfo'],
                    'account_number' => $row['account_number'],
                    'currency' => $row['currency'],
                    'document_number' => $row['document_number'],
                    'operation_date' => $row['operation_date'],
                    'bank_mfo' => $row['bank_mfo'],
                    'bank_name' => $row['bank_name'],
                    'correspondent_account' => $row['correspondent_account'],
                    'correspondent_edrpou' => $row['correspondent_edrpou'],
                    'correspondent_name' => $row['correspondent_name'],
                    'amount' => $row['amount'],
                    'payment_purpose' => $row['payment_purpose'],
                    'direction' => $row['direction'],
                    'status' => $row['status'],
                    'raw_payload' => $row['raw_payload'],
                    'suggested_student_ids' => $row['suggested_student_ids'],
                ]);

                if ($rowStatement->fetchColumn() !== false) {
                    $newRows++;
                }
            }

            $duplicateRows = max(0, (int) $batchData['total_rows'] - $newRows);

            $updateBatchStatement = $pdo->prepare(
                'UPDATE statement_import_batches
                 SET new_rows = :new_rows,
                     duplicate_rows = :duplicate_rows
                 WHERE id = :id'
            );

            $updateBatchStatement->execute([
                'id' => $batchId,
                'new_rows' => $newRows,
                'duplicate_rows' => $duplicateRows,
            ]);

            $pdo->commit();

            return [
                'batch_id' => $batchId,
                'new_rows' => $newRows,
                'duplicate_rows' => $duplicateRows,
            ];
        } catch (Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    public function batchSummaries(): array
    {
        $statement = Database::connection()->query(
            "SELECT
                b.id,
                b.original_filename,
                b.account_number,
                b.currency,
                b.total_rows,
                b.new_rows,
                b.duplicate_rows,
                b.created_at,
                COALESCE(SUM(CASE WHEN r.status = 'new' THEN 1 ELSE 0 END), 0) AS rows_new,
                COALESCE(SUM(CASE WHEN r.status = 'unmatched' THEN 1 ELSE 0 END), 0) AS rows_unmatched,
                COALESCE(SUM(CASE WHEN r.status = 'draft_created' THEN 1 ELSE 0 END), 0) AS rows_draft_created,
                COALESCE(SUM(CASE WHEN r.status = 'ignored' THEN 1 ELSE 0 END), 0) AS rows_ignored,
                COALESCE(SUM(CASE WHEN r.status = 'deleted' THEN 1 ELSE 0 END), 0) AS rows_deleted
             FROM statement_import_batches b
             LEFT JOIN statement_import_rows r ON r.batch_id = b.id
             GROUP BY b.id
             ORDER BY b.created_at DESC, b.id DESC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listRows(?int $batchId, ?string $status): array
    {
        $sql = <<<'SQL'
            SELECT
                r.id,
                r.batch_id,
                r.row_number,
                r.document_number,
                r.operation_date,
                r.correspondent_name,
                r.correspondent_account,
                r.correspondent_edrpou,
                r.bank_name,
                r.bank_mfo,
                r.amount,
                r.currency,
                r.payment_purpose,
                r.direction,
                r.status,
                r.suggested_student_ids::text AS suggested_student_ids,
                r.matched_student_id,
                r.payment_id,
                r.expense_id,
                r.payout_id,
                r.created_document_type,
                r.review_note,
                r.created_at,
                r.updated_at,
                b.original_filename,
                b.created_at AS batch_created_at,
                s.first_name AS matched_first_name,
                s.family_name AS matched_family_name,
                s.father_name AS matched_father_name,
                ec.name AS expense_category_name,
                pst.first_name AS payout_staff_first_name,
                pst.family_name AS payout_staff_family_name,
                pst.father_name AS payout_staff_father_name
             FROM statement_import_rows r
             INNER JOIN statement_import_batches b ON b.id = r.batch_id
             LEFT JOIN students s ON s.id = r.matched_student_id
             LEFT JOIN expenses e ON e.id = r.expense_id
             LEFT JOIN expense_categories ec ON ec.id = e.category_id
             LEFT JOIN staff_payouts sp ON sp.id = r.payout_id
             LEFT JOIN staff pst ON pst.id = sp.staff_id
        SQL;

        $conditions = [];
        $params = [];

        if ($batchId !== null) {
            $conditions[] = 'r.batch_id = :batch_id';
            $params['batch_id'] = $batchId;
        }

        if ($status !== null && $status !== '') {
            $conditions[] = 'r.status = :status';
            $params['status'] = $status;
        } else {
            $conditions[] = "r.status IN ('new', 'unmatched')";
        }

        if ($conditions !== []) {
            $sql .= ' WHERE '.implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY r.operation_date DESC, r.id DESC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findRowById(int $rowId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                r.*,
                r.suggested_student_ids::text AS suggested_student_ids_text
             FROM statement_import_rows r
             WHERE r.id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $rowId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function updateRowStatus(int $rowId, string $status, int $reviewedByUserId, ?string $reviewNote = null): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE statement_import_rows
             SET status = :status,
                 review_note = :review_note,
                 reviewed_by_user_id = :reviewed_by_user_id,
                 reviewed_at = CURRENT_TIMESTAMP,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $rowId,
            'status' => $status,
            'review_note' => $reviewNote,
            'reviewed_by_user_id' => $reviewedByUserId,
        ]);
    }

    public function createPaymentDraftFromRow(int $rowId, int $studentId, string $coveredMonth, int $reviewedByUserId): int
    {
        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();

            $row = $this->lockImportRow($pdo, $rowId);

            if ((string) $row['direction'] !== 'incoming') {
                throw new \RuntimeException('Only incoming rows can create payment drafts.');
            }

            $paymentStatement = $pdo->prepare(
                'INSERT INTO payments (
                    student_id,
                    payment_date,
                    amount,
                    method,
                    source,
                    external_reference,
                    status,
                    covered_month,
                    import_row_id,
                    comment,
                    created_at
                 ) VALUES (
                    :student_id,
                    :payment_date,
                    :amount,
                    :method,
                    :source,
                    :external_reference,
                    :status,
                    :covered_month,
                    :import_row_id,
                    :comment,
                    CURRENT_TIMESTAMP
                 )
                 RETURNING id'
            );

            $paymentStatement->execute([
                'student_id' => $studentId,
                'payment_date' => (string) $row['operation_date'].' 00:00:00',
                'amount' => $row['amount'],
                'method' => 'bank_transfer',
                'source' => 'bank_statement',
                'external_reference' => $row['document_number'] ?: null,
                'status' => 'pending',
                'covered_month' => $coveredMonth,
                'import_row_id' => $rowId,
                'comment' => trim(
                    'Imported bank statement draft. '
                    .((string) $row['correspondent_name'] !== '' ? 'Correspondent: '.$row['correspondent_name'].'. ' : '')
                    .((string) $row['payment_purpose'] !== '' ? 'Purpose: '.$row['payment_purpose'] : '')
                ),
            ]);

            $paymentId = (int) $paymentStatement->fetchColumn();

            $this->markRowAsCreated($pdo, $rowId, 'payment', $reviewedByUserId, [
                'matched_student_id' => $studentId,
                'payment_id' => $paymentId,
            ]);

            $pdo->commit();

            return $paymentId;
        } catch (Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    public function createExpenseDraftFromRow(int $rowId, int $categoryId, int $paidFromAccountId, int $reviewedByUserId): int
    {
        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();

            $row = $this->lockImportRow($pdo, $rowId);

            if ((string) $row['direction'] !== 'outgoing') {
                throw new \RuntimeException('Only outgoing rows can create expense drafts.');
            }

            $statement = $pdo->prepare(
                'INSERT INTO expenses (
                    expense_date,
                    category_id,
                    amount,
                    paid_from_account_id,
                    staff_id,
                    status,
                    description,
                    reason,
                    created_at,
                    posted_at,
                    import_row_id
                 ) VALUES (
                    :expense_date,
                    :category_id,
                    :amount,
                    :paid_from_account_id,
                    NULL,
                    :status,
                    :description,
                    :reason,
                    CURRENT_TIMESTAMP,
                    NULL,
                    :import_row_id
                 )
                 RETURNING id'
            );

            $statement->execute([
                'expense_date' => (string) $row['operation_date'].' 00:00:00',
                'category_id' => $categoryId,
                'amount' => $row['amount'],
                'paid_from_account_id' => $paidFromAccountId,
                'status' => 'draft',
                'description' => $this->buildImportedDescription($row),
                'reason' => 'Imported from bank statement',
                'import_row_id' => $rowId,
            ]);

            $expenseId = (int) $statement->fetchColumn();

            $this->markRowAsCreated($pdo, $rowId, 'expense', $reviewedByUserId, [
                'expense_id' => $expenseId,
            ]);

            $pdo->commit();

            return $expenseId;
        } catch (Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    public function createPayoutDraftFromRow(int $rowId, int $staffId, int $reviewedByUserId): int
    {
        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();

            $row = $this->lockImportRow($pdo, $rowId);

            if ((string) $row['direction'] !== 'outgoing') {
                throw new \RuntimeException('Only outgoing rows can create payout drafts.');
            }

            $statement = $pdo->prepare(
                'INSERT INTO staff_payouts (
                    staff_id,
                    payout_date,
                    amount,
                    status,
                    created_at,
                    posted_at,
                    comment,
                    import_row_id
                 ) VALUES (
                    :staff_id,
                    :payout_date,
                    :amount,
                    :status,
                    CURRENT_TIMESTAMP,
                    NULL,
                    :comment,
                    :import_row_id
                 )
                 RETURNING id'
            );

            $statement->execute([
                'staff_id' => $staffId,
                'payout_date' => (string) $row['operation_date'].' 00:00:00',
                'amount' => $row['amount'],
                'status' => 'draft',
                'comment' => 'Imported bank statement payout draft. '.$this->buildImportedDescription($row),
                'import_row_id' => $rowId,
            ]);

            $payoutId = (int) $statement->fetchColumn();

            $this->markRowAsCreated($pdo, $rowId, 'payout', $reviewedByUserId, [
                'payout_id' => $payoutId,
            ]);

            $pdo->commit();

            return $payoutId;
        } catch (Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    public function studentDirectory(): array
    {
        $statement = Database::connection()->query(
            'SELECT
                s.id,
                s.first_name,
                s.family_name,
                s.father_name,
                s.status,
                p.name AS plan_name,
                st.family_name AS staff_family_name,
                st.first_name AS staff_first_name,
                st.father_name AS staff_father_name
             FROM students s
             INNER JOIN plans p ON p.id = s.plan_id
             INNER JOIN staff st ON st.id = s.staff_id
             ORDER BY s.family_name ASC NULLS LAST, s.first_name ASC, s.father_name ASC NULLS LAST, s.id ASC'
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function expenseCategories(): array
    {
        $statement = Database::connection()->query(
            'SELECT id, code, name
             FROM expense_categories
             WHERE is_active = TRUE
             ORDER BY name ASC, id ASC'
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paidFromAccounts(): array
    {
        $statement = Database::connection()->query(
            "SELECT id, code, name
             FROM accounts
             WHERE is_active = TRUE
               AND type = 'asset'
             ORDER BY code ASC, id ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function activeStaff(): array
    {
        $statement = Database::connection()->query(
            "SELECT id, role, first_name, family_name, father_name
             FROM staff
             WHERE status = 'active'
             ORDER BY family_name ASC NULLS LAST, first_name ASC, father_name ASC NULLS LAST, id ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function lockImportRow(PDO $pdo, int $rowId): array
    {
        $statement = $pdo->prepare(
            'SELECT id, operation_date, amount, document_number, correspondent_name, payment_purpose, direction, status, payment_id, expense_id, payout_id, created_document_type
             FROM statement_import_rows
             WHERE id = :id
             LIMIT 1
             FOR UPDATE'
        );

        $statement->execute(['id' => $rowId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (! is_array($row)) {
            throw new \RuntimeException('Imported row not found.');
        }

        if (! in_array((string) $row['status'], ['new', 'unmatched'], true)) {
            throw new \RuntimeException('Only unresolved queue rows can create drafts.');
        }

        if (
            $row['payment_id'] !== null
            || $row['expense_id'] !== null
            || $row['payout_id'] !== null
            || (string) ($row['created_document_type'] ?? '') !== ''
        ) {
            throw new \RuntimeException('This row is already linked to a business document.');
        }

        return $row;
    }

    private function markRowAsCreated(PDO $pdo, int $rowId, string $documentType, int $reviewedByUserId, array $ids): void
    {
        $statement = $pdo->prepare(
            'UPDATE statement_import_rows
             SET status = :status,
                 matched_student_id = :matched_student_id,
                 payment_id = :payment_id,
                 expense_id = :expense_id,
                 payout_id = :payout_id,
                 created_document_type = :created_document_type,
                 reviewed_by_user_id = :reviewed_by_user_id,
                 reviewed_at = CURRENT_TIMESTAMP,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $rowId,
            'status' => 'draft_created',
            'matched_student_id' => $ids['matched_student_id'] ?? null,
            'payment_id' => $ids['payment_id'] ?? null,
            'expense_id' => $ids['expense_id'] ?? null,
            'payout_id' => $ids['payout_id'] ?? null,
            'created_document_type' => $documentType,
            'reviewed_by_user_id' => $reviewedByUserId,
        ]);
    }

    private function buildImportedDescription(array $row): string
    {
        return trim(
            ((string) ($row['correspondent_name'] ?? '') !== '' ? 'Correspondent: '.$row['correspondent_name'].'. ' : '')
            .((string) ($row['payment_purpose'] ?? '') !== '' ? 'Purpose: '.$row['payment_purpose'] : '')
        );
    }
}

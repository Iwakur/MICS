<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class PayoutRepository
{
    public function teacherSuggestionSummary(int $staffId, string $nextMonthStart): array
    {
        $statement = Database::connection()->prepare(
            "SELECT
                COUNT(s.id) AS student_count,
                COALESCE(SUM(p.lesson_count * p.teacher_share_per_lesson), 0) AS suggested_amount
             FROM students s
             INNER JOIN plans p ON p.id = s.plan_id
             WHERE s.staff_id = :staff_id
               AND s.status = 'active'
               AND s.joined_at < :next_month_start"
        );

        $statement->execute([
            'staff_id' => $staffId,
            'next_month_start' => $nextMonthStart,
        ]);

        $summary = $statement->fetch(PDO::FETCH_ASSOC);

        return [
            'student_count' => (int) ($summary['student_count'] ?? 0),
            'suggested_amount' => (float) ($summary['suggested_amount'] ?? 0),
        ];
    }

    public function teacherSuggestionStudents(int $staffId, string $nextMonthStart): array
    {
        $statement = Database::connection()->prepare(
            "SELECT
                s.id,
                s.first_name,
                s.family_name,
                s.father_name,
                s.joined_at,
                p.name AS plan_name,
                p.lesson_count * p.teacher_share_per_lesson AS monthly_teacher_share
             FROM students s
             INNER JOIN plans p ON p.id = s.plan_id
             WHERE s.staff_id = :staff_id
               AND s.status = 'active'
               AND s.joined_at < :next_month_start
             ORDER BY s.family_name ASC NULLS LAST, s.first_name ASC, s.father_name ASC NULLS LAST, s.id ASC"
        );

        $statement->execute([
            'staff_id' => $staffId,
            'next_month_start' => $nextMonthStart,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function teacherHistory(int $staffId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, payout_date, amount, status, posted_at, comment
             FROM staff_payouts
             WHERE staff_id = :staff_id
             ORDER BY payout_date DESC, id DESC'
        );

        $statement->execute(['staff_id' => $staffId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function adminSuggestions(string $nextMonthStart): array
    {
        $statement = Database::connection()->prepare(
            "SELECT
                st.id,
                st.role,
                st.first_name,
                st.family_name,
                st.father_name,
                st.fixed_salary_amount,
                COUNT(s.id) AS student_count,
                CASE
                    WHEN LOWER(st.role) = 'teacher' THEN COALESCE(SUM(p.lesson_count * p.teacher_share_per_lesson), 0)
                    ELSE COALESCE(st.fixed_salary_amount, 0)
                END AS suggested_amount
             FROM staff st
             LEFT JOIN students s
               ON s.staff_id = st.id
              AND s.status = 'active'
              AND s.joined_at < :next_month_start
             LEFT JOIN plans p ON p.id = s.plan_id
             WHERE st.status = 'active'
             GROUP BY st.id, st.role, st.first_name, st.family_name, st.father_name, st.fixed_salary_amount
             ORDER BY st.family_name ASC NULLS LAST, st.first_name ASC, st.father_name ASC NULLS LAST, st.id ASC"
        );

        $statement->execute([
            'next_month_start' => $nextMonthStart,
        ]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function payoutHistory(?string $status, ?int $staffId): array
    {
        $conditions = [];
        $params = [];

        if ($status !== null && $status !== '') {
            $conditions[] = 'sp.status = :status';
            $params['status'] = $status;
        }

        if ($staffId !== null && $staffId > 0) {
            $conditions[] = 'sp.staff_id = :staff_id';
            $params['staff_id'] = $staffId;
        }

        $sql = <<<'SQL'
            SELECT
                sp.id,
                sp.staff_id,
                sp.payout_date,
                sp.amount,
                sp.status,
                sp.posted_at,
                sp.comment,
                sp.import_row_id,
                st.role,
                st.first_name,
                st.family_name,
                st.father_name
            FROM staff_payouts sp
            INNER JOIN staff st ON st.id = sp.staff_id
        SQL;

        if ($conditions !== []) {
            $sql .= ' WHERE '.implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY sp.payout_date DESC, sp.id DESC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findPayoutById(int $payoutId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, staff_id, payout_date, amount, status, posted_at, comment, import_row_id
             FROM staff_payouts
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $payoutId]);
        $payout = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($payout) ? $payout : null;
    }

    public function monthRecordExists(int $staffId, string $monthStart, string $nextMonthStart): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT 1
             FROM staff_payouts
             WHERE staff_id = :staff_id
               AND payout_date >= :month_start
               AND payout_date < :next_month_start
             LIMIT 1'
        );

        $statement->execute([
            'staff_id' => $staffId,
            'month_start' => $monthStart,
            'next_month_start' => $nextMonthStart,
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function createDraft(int $staffId, float $amount, string $payoutDate, ?string $comment = null, ?int $importRowId = null): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO staff_payouts (staff_id, payout_date, amount, status, created_at, posted_at, comment, import_row_id)
             VALUES (:staff_id, :payout_date, :amount, :status, CURRENT_TIMESTAMP, NULL, :comment, :import_row_id)
             RETURNING id'
        );

        $statement->execute([
            'staff_id' => $staffId,
            'payout_date' => $payoutDate,
            'amount' => $amount,
            'status' => 'draft',
            'comment' => $comment,
            'import_row_id' => $importRowId,
        ]);

        return (int) $statement->fetchColumn();
    }

    public function updateDraft(int $payoutId, array $data): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE staff_payouts
             SET staff_id = :staff_id,
                 payout_date = :payout_date,
                 amount = :amount,
                 comment = :comment
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $payoutId,
            'staff_id' => $data['staff_id'],
            'payout_date' => $data['payout_date'],
            'amount' => $data['amount'],
            'comment' => $data['comment'],
        ]);
    }

    public function postDraft(int $payoutId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE staff_payouts
             SET status = 'posted',
                 posted_at = CURRENT_TIMESTAMP
             WHERE id = :id"
        );

        $statement->execute(['id' => $payoutId]);
    }

    public function voidPayout(int $payoutId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE staff_payouts
             SET status = 'void',
                 posted_at = NULL
             WHERE id = :id"
        );

        $statement->execute(['id' => $payoutId]);
    }

    public function activeStaff(): array
    {
        $statement = Database::connection()->query(
            "SELECT id, role, first_name, family_name, father_name, fixed_salary_amount
             FROM staff
             WHERE status = 'active'
             ORDER BY family_name ASC NULLS LAST, first_name ASC, father_name ASC NULLS LAST, id ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class PaymentRepository
{
    public function list(?string $status, ?int $studentId): array
    {
        $sql = <<<'SQL'
            SELECT
                p.id,
                p.student_id,
                p.payment_date,
                p.amount,
                p.method,
                p.source,
                p.external_reference,
                p.status,
                p.covered_month,
                p.import_row_id,
                p.created_at,
                p.confirmed_at,
                p.comment,
                s.first_name,
                s.family_name,
                s.father_name
            FROM payments p
            INNER JOIN students s ON s.id = p.student_id
        SQL;

        $conditions = [];
        $params = [];

        if ($status !== null && $status !== '') {
            $conditions[] = 'p.status = :status';
            $params['status'] = $status;
        }

        if ($studentId !== null && $studentId > 0) {
            $conditions[] = 'p.student_id = :student_id';
            $params['student_id'] = $studentId;
        }

        if ($conditions !== []) {
            $sql .= ' WHERE '.implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY p.payment_date DESC, p.id DESC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $paymentId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, student_id, payment_date, amount, method, source, external_reference, status, covered_month, import_row_id, comment
             FROM payments
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $paymentId]);
        $payment = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($payment) ? $payment : null;
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
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
                created_at,
                confirmed_at
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
                CURRENT_TIMESTAMP,
                :confirmed_at
             )
             RETURNING id'
        );

        $statement->execute($this->paymentParams($data));

        return (int) $statement->fetchColumn();
    }

    public function update(int $paymentId, array $data): void
    {
        $params = $this->paymentParams($data);
        $params['id'] = $paymentId;

        $statement = Database::connection()->prepare(
            'UPDATE payments
             SET student_id = :student_id,
                 payment_date = :payment_date,
                 amount = :amount,
                 method = :method,
                 source = :source,
                 external_reference = :external_reference,
                 status = :status,
                 covered_month = :covered_month,
                 comment = :comment,
                 confirmed_at = :confirmed_at
             WHERE id = :id'
        );

        $statement->execute($params);
    }

    public function confirm(int $paymentId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE payments
             SET status = 'confirmed',
                 confirmed_at = CURRENT_TIMESTAMP
             WHERE id = :id"
        );

        $statement->execute(['id' => $paymentId]);
    }

    public function void(int $paymentId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE payments
             SET status = 'void',
                 confirmed_at = NULL
             WHERE id = :id"
        );

        $statement->execute(['id' => $paymentId]);
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

    private function paymentParams(array $data): array
    {
        return [
            'student_id' => $data['student_id'],
            'payment_date' => $data['payment_date'],
            'amount' => $data['amount'],
            'method' => $data['method'],
            'source' => $data['source'],
            'external_reference' => $data['external_reference'],
            'status' => $data['status'],
            'covered_month' => $data['covered_month'],
            'import_row_id' => $data['import_row_id'],
            'comment' => $data['comment'],
            'confirmed_at' => $data['confirmed_at'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class ExpenseRepository
{
    public function list(?string $status, ?int $categoryId): array
    {
        $sql = <<<'SQL'
            SELECT
                e.id,
                e.expense_date,
                e.category_id,
                e.amount,
                e.paid_from_account_id,
                e.staff_id,
                e.status,
                e.description,
                e.reason,
                e.created_at,
                e.posted_at,
                e.import_row_id,
                c.code AS category_code,
                c.name AS category_name,
                a.code AS account_code,
                a.name AS account_name,
                st.first_name,
                st.family_name,
                st.father_name
            FROM expenses e
            INNER JOIN expense_categories c ON c.id = e.category_id
            INNER JOIN accounts a ON a.id = e.paid_from_account_id
            LEFT JOIN staff st ON st.id = e.staff_id
        SQL;

        $conditions = [];
        $params = [];

        if ($status !== null && $status !== '') {
            $conditions[] = 'e.status = :status';
            $params['status'] = $status;
        }

        if ($categoryId !== null && $categoryId > 0) {
            $conditions[] = 'e.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        if ($conditions !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY e.expense_date DESC, e.id DESC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $expenseId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, expense_date, category_id, amount, paid_from_account_id, staff_id, status, description, reason, import_row_id
             FROM expenses
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $expenseId]);
        $expense = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($expense) ? $expense : null;
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
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
                :staff_id,
                :status,
                :description,
                :reason,
                CURRENT_TIMESTAMP,
                :posted_at,
                :import_row_id
             )
             RETURNING id'
        );

        $statement->execute($this->expenseParams($data));

        return (int) $statement->fetchColumn();
    }

    public function update(int $expenseId, array $data): void
    {
        $params = $this->expenseParams($data);
        $params['id'] = $expenseId;

        $statement = Database::connection()->prepare(
            'UPDATE expenses
             SET expense_date = :expense_date,
                 category_id = :category_id,
                 amount = :amount,
                 paid_from_account_id = :paid_from_account_id,
                 staff_id = :staff_id,
                 status = :status,
                 description = :description,
                 reason = :reason,
                 posted_at = :posted_at
             WHERE id = :id'
        );

        $statement->execute($params);
    }

    public function post(int $expenseId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE expenses
             SET status = 'posted',
                 posted_at = CURRENT_TIMESTAMP
             WHERE id = :id"
        );

        $statement->execute(['id' => $expenseId]);
    }

    public function void(int $expenseId): void
    {
        $statement = Database::connection()->prepare(
            "UPDATE expenses
             SET status = 'void',
                 posted_at = NULL
             WHERE id = :id"
        );

        $statement->execute(['id' => $expenseId]);
    }

    public function categories(): array
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
            "SELECT id, first_name, family_name, father_name
             FROM staff
             WHERE status = 'active'
             ORDER BY family_name ASC NULLS LAST, first_name ASC, father_name ASC NULLS LAST, id ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function expenseParams(array $data): array
    {
        return [
            'expense_date' => $data['expense_date'],
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
            'paid_from_account_id' => $data['paid_from_account_id'],
            'staff_id' => $data['staff_id'],
            'status' => $data['status'],
            'description' => $data['description'],
            'reason' => $data['reason'],
            'posted_at' => $data['posted_at'],
            'import_row_id' => $data['import_row_id'],
        ];
    }
}

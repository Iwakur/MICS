<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class StaffRepository
{
    public function findForList(?string $search, ?string $status): array
    {
        $conditions = [];
        $params = [];

        if (is_string($status) && $status !== '') {
            $conditions[] = 'status = :status';
            $params['status'] = $status;
        }

        if (is_string($search) && trim($search) !== '') {
            $conditions[] = "(LOWER(CONCAT_WS(' ', family_name, first_name, father_name, role)) LIKE :search OR LOWER(COALESCE(phone, '')) LIKE :search OR LOWER(COALESCE(email, '')) LIKE :search)";
            $params['search'] = '%'.strtolower(trim($search)).'%';
        }

        $sql = 'SELECT id, role, first_name, family_name, father_name, status, payout_card_number, fixed_salary_amount, phone, email, comments FROM staff';

        if ($conditions !== []) {
            $sql .= ' WHERE '.implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY family_name ASC NULLS LAST, first_name ASC, father_name ASC NULLS LAST, id ASC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $staffId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, role, first_name, family_name, father_name, status, payout_card_number, fixed_salary_amount, phone, email, comments
             FROM staff
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $staffId]);
        $staff = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($staff) ? $staff : null;
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO staff (role, first_name, family_name, father_name, status, payout_card_number, fixed_salary_amount, phone, email, comments, created_at, updated_at)
             VALUES (:role, :first_name, :family_name, :father_name, :status, :payout_card_number, :fixed_salary_amount, :phone, :email, :comments, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
             RETURNING id'
        );

        $statement->execute($this->staffParams($data));

        return (int) $statement->fetchColumn();
    }

    public function update(int $staffId, array $data): void
    {
        $params = $this->staffParams($data);
        $params['id'] = $staffId;

        $statement = Database::connection()->prepare(
            'UPDATE staff
             SET role = :role,
                 first_name = :first_name,
                 family_name = :family_name,
                 father_name = :father_name,
                 status = :status,
                 payout_card_number = :payout_card_number,
                 fixed_salary_amount = :fixed_salary_amount,
                 phone = :phone,
                 email = :email,
                 comments = :comments,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $statement->execute($params);
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

    private function staffParams(array $data): array
    {
        return [
            'role' => $data['role'],
            'first_name' => $data['first_name'],
            'family_name' => $data['family_name'],
            'father_name' => $data['father_name'],
            'status' => $data['status'],
            'payout_card_number' => $data['payout_card_number'],
            'fixed_salary_amount' => $data['fixed_salary_amount'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'comments' => $data['comments'],
        ];
    }
}

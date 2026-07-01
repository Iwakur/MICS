<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class StudentRepository
{
    public function findForAdminList(?string $search, ?string $status): array
    {
        $pdo = Database::connection();
        $sql = <<<'SQL'
            SELECT
                s.id,
                s.first_name,
                s.family_name,
                s.father_name,
                s.phone,
                s.email,
                s.status,
                s.discount_amount,
                s.joined_at,
                p.name AS plan_name,
                st.family_name AS staff_family_name,
                st.first_name AS staff_first_name,
                st.father_name AS staff_father_name
            FROM students s
            INNER JOIN plans p ON p.id = s.plan_id
            INNER JOIN staff st ON st.id = s.staff_id
        SQL;

        [$whereSql, $params] = $this->buildListFilters($search, $status, null);
        $sql .= $whereSql . ' ORDER BY s.created_at DESC, s.id DESC';

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findForTeacherList(int $staffId, ?string $search, ?string $status): array
    {
        $pdo = Database::connection();
        $sql = <<<'SQL'
            SELECT
                s.id,
                s.first_name,
                s.family_name,
                s.father_name,
                s.phone,
                s.email,
                s.status,
                s.discount_amount,
                s.joined_at,
                p.name AS plan_name
            FROM students s
            INNER JOIN plans p ON p.id = s.plan_id
        SQL;

        [$whereSql, $params] = $this->buildListFilters($search, $status, $staffId);
        $sql .= $whereSql . ' ORDER BY s.created_at DESC, s.id DESC';

        $statement = $pdo->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByIdForAdmin(int $studentId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, first_name, family_name, father_name, phone, email, status, plan_id, staff_id, discount_amount, joined_at, comments
             FROM students
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $studentId]);
        $student = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($student) ? $student : null;
    }

    public function findByIdForTeacher(int $studentId, int $staffId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, first_name, family_name, father_name, phone, email, status, plan_id, staff_id, discount_amount, joined_at, comments
             FROM students
             WHERE id = :id AND staff_id = :staff_id
             LIMIT 1'
        );

        $statement->execute([
            'id' => $studentId,
            'staff_id' => $staffId,
        ]);

        $student = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($student) ? $student : null;
    }

    public function create(array $data): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO students (first_name, family_name, father_name, phone, email, status, plan_id, staff_id, discount_amount, joined_at, comments, created_at, updated_at)
             VALUES (:first_name, :family_name, :father_name, :phone, :email, :status, :plan_id, :staff_id, :discount_amount, :joined_at, :comments, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
             RETURNING id'
        );

        $statement->execute($this->studentParams($data));

        return (int) $statement->fetchColumn();
    }

    public function update(int $studentId, array $data): void
    {
        $params = $this->studentParams($data);
        $params['id'] = $studentId;

        $statement = Database::connection()->prepare(
            'UPDATE students
             SET first_name = :first_name,
                 family_name = :family_name,
                 father_name = :father_name,
                 phone = :phone,
                 email = :email,
                 status = :status,
                 plan_id = :plan_id,
                 staff_id = :staff_id,
                 discount_amount = :discount_amount,
                 joined_at = :joined_at,
                 comments = :comments,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $statement->execute($params);
    }

    public function activePlans(): array
    {
        $statement = Database::connection()->query(
            'SELECT id, name
             FROM plans
             WHERE is_assignable = TRUE
             ORDER BY name ASC, id ASC'
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function activePlansIncluding(?int $planId): array
    {
        if ($planId === null || $planId <= 0) {
            return $this->activePlans();
        }

        $statement = Database::connection()->prepare(
            'SELECT id, name
             FROM plans
             WHERE is_assignable = TRUE OR id = :id
             ORDER BY is_assignable DESC, name ASC, id ASC'
        );

        $statement->execute(['id' => $planId]);

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

    private function buildListFilters(?string $search, ?string $status, ?int $staffId): array
    {
        $conditions = [];
        $params = [];

        if ($staffId !== null) {
            $conditions[] = 's.staff_id = :staff_id';
            $params['staff_id'] = $staffId;
        }

        if (is_string($status) && $status !== '') {
            $conditions[] = 's.status = :status';
            $params['status'] = $status;
        }

        if (is_string($search) && trim($search) !== '') {
            $conditions[] = "(LOWER(CONCAT_WS(' ', s.family_name, s.first_name, s.father_name)) LIKE :search OR LOWER(COALESCE(s.phone, '')) LIKE :search OR LOWER(COALESCE(s.email, '')) LIKE :search)";
            $params['search'] = '%' . strtolower(trim($search)) . '%';
        }

        if ($conditions === []) {
            return ['', $params];
        }

        return [' WHERE ' . implode(' AND ', $conditions), $params];
    }

    private function studentParams(array $data): array
    {
        return [
            'first_name' => $data['first_name'],
            'family_name' => $data['family_name'],
            'father_name' => $data['father_name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'status' => $data['status'],
            'plan_id' => $data['plan_id'],
            'staff_id' => $data['staff_id'],
            'discount_amount' => $data['discount_amount'],
            'joined_at' => $data['joined_at'],
            'comments' => $data['comments'],
        ];
    }
}

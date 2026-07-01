<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class UserRepository
{
    public function findForList(?string $search, ?string $role, ?string $isActive, ?int $staffId): array
    {
        $sql = <<<'SQL'
            SELECT
                u.id,
                u.staff_id,
                u.username,
                u.role,
                u.is_active,
                u.last_login_at,
                u.created_at,
                u.updated_at,
                s.role AS staff_role,
                s.first_name AS staff_first_name,
                s.family_name AS staff_family_name,
                s.father_name AS staff_father_name,
                s.status AS staff_status
            FROM users u
            LEFT JOIN staff s ON s.id = u.staff_id
        SQL;

        [$whereSql, $params] = $this->buildListFilters($search, $role, $isActive, $staffId);
        $sql .= $whereSql . ' ORDER BY u.created_at DESC, u.id DESC';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, staff_id, username, role, is_active, last_login_at, created_at, updated_at
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $userId]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($user) ? $user : null;
    }

    public function findWithStaffById(int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                u.id,
                u.staff_id,
                u.username,
                u.role,
                u.is_active,
                u.last_login_at,
                u.created_at,
                u.updated_at,
                s.role AS staff_role,
                s.first_name AS staff_first_name,
                s.family_name AS staff_family_name,
                s.father_name AS staff_father_name,
                s.status AS staff_status
             FROM users u
             LEFT JOIN staff s ON s.id = u.staff_id
             WHERE u.id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $userId]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($user) ? $user : null;
    }

    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM users WHERE LOWER(username) = LOWER(:username)';
        $params = ['username' => $username];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }

        $sql .= ' LIMIT 1';

        $statement = Database::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchColumn() !== false;
    }

    public function create(array $data, string $password): int
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO users (staff_id, username, password_hash, role, is_active, created_at, updated_at)
             VALUES (:staff_id, :username, :password_hash, :role, :is_active, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
             RETURNING id'
        );

        $params = $this->userParams($data);
        $params['password_hash'] = password_hash($password, PASSWORD_DEFAULT);

        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public function update(int $userId, array $data): void
    {
        $params = $this->userParams($data);
        $params['id'] = $userId;

        $statement = Database::connection()->prepare(
            'UPDATE users
             SET staff_id = :staff_id,
                 username = :username,
                 role = :role,
                 is_active = :is_active,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $statement->execute($params);
    }

    public function resetPassword(int $userId, string $password): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE users
             SET password_hash = :password_hash,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $userId,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    public function staffOptions(): array
    {
        $statement = Database::connection()->query(
            "SELECT id, role, first_name, family_name, father_name, status
             FROM staff
             ORDER BY CASE WHEN status = 'active' THEN 0 ELSE 1 END, family_name ASC NULLS LAST, first_name ASC, father_name ASC NULLS LAST, id ASC"
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildListFilters(?string $search, ?string $role, ?string $isActive, ?int $staffId): array
    {
        $conditions = [];
        $params = [];

        if (is_string($role) && $role !== '') {
            $conditions[] = 'u.role = :role';
            $params['role'] = $role;
        }

        if ($isActive === '1' || $isActive === '0') {
            $conditions[] = 'u.is_active = :is_active';
            $params['is_active'] = $isActive === '1' ? 'true' : 'false';
        }

        if ($staffId !== null && $staffId > 0) {
            $conditions[] = 'u.staff_id = :staff_id';
            $params['staff_id'] = $staffId;
        }

        if (is_string($search) && trim($search) !== '') {
            $conditions[] = "(LOWER(u.username) LIKE :search OR LOWER(CONCAT_WS(' ', s.family_name, s.first_name, s.father_name, s.role)) LIKE :search)";
            $params['search'] = '%' . strtolower(trim($search)) . '%';
        }

        if ($conditions === []) {
            return ['', $params];
        }

        return [' WHERE ' . implode(' AND ', $conditions), $params];
    }

    private function userParams(array $data): array
    {
        return [
            'staff_id' => $data['staff_id'],
            'username' => $data['username'],
            'role' => $data['role'],
            'is_active' => $data['is_active'] ? 'true' : 'false',
        ];
    }
}

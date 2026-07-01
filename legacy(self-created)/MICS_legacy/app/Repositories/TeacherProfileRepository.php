<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class TeacherProfileRepository
{
    public function findByUserId(int $userId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT
                u.id,
                u.username,
                u.role AS access_role,
                u.profile_image_path AS user_profile_image_path,
                u.last_login_at,
                u.created_at,
                s.id AS staff_id,
                s.role AS staff_role,
                s.first_name,
                s.family_name,
                s.father_name,
                s.email,
                s.phone,
                s.profile_image_path AS staff_profile_image_path
             FROM users u
             INNER JOIN staff s ON s.id = u.staff_id
             WHERE u.id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $userId]);
        $profile = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($profile) ? $profile : null;
    }

    public function verifyCurrentPassword(int $userId, string $password): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT password_hash
             FROM users
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $userId]);
        $hash = $statement->fetchColumn();

        return is_string($hash) && $hash !== '' && password_verify($password, $hash);
    }

    public function updatePassword(int $userId, string $newPassword): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE users
             SET password_hash = :password_hash,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        $statement->execute([
            'id' => $userId,
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);
    }
}

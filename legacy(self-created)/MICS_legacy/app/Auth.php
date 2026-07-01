<?php

declare(strict_types=1);

namespace App;

use PDO;

final class Auth
{
    public static function attempt(string $username, string $password): bool
    {
        $sql = <<<'SQL'
            SELECT u.*, s.first_name AS staff_first_name, s.family_name AS staff_family_name, s.father_name AS staff_father_name
            FROM users u
            LEFT JOIN staff s ON s.id = u.staff_id
            WHERE u.username = :username AND u.is_active = TRUE
            LIMIT 1
        SQL;

        $statement = Database::connection()->prepare($sql);
        $statement->execute(['username' => $username]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);

        if (! $user || ! password_verify($password, $user['password_hash'])) {
            return false;
        }

        Database::connection()->prepare(
            'UPDATE users SET last_login_at = NOW(), updated_at = NOW() WHERE id = :id'
        )->execute(['id' => $user['id']]);

        $_SESSION['auth_user'] = [
            'id' => (int) $user['id'],
            'staff_id' => $user['staff_id'] !== null ? (int) $user['staff_id'] : null,
            'username' => $user['username'],
            'role' => $user['role'],
            'profile_image_path' => $user['profile_image_path'],
            'staff_first_name' => $user['staff_first_name'],
            'staff_family_name' => $user['staff_family_name'],
            'staff_father_name' => $user['staff_father_name'],
        ];

        return true;
    }

    public static function user(): ?array
    {
        return $_SESSION['auth_user'] ?? null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function requireAuth(): void
    {
        if (! self::check()) {
            flash('error', 'Please log in first.');
            redirect('login.php');
        }
    }

    public static function requireAdmin(): void
    {
        self::requireAuth();

        if (! self::isAdmin()) {
            self::logout();
            flash('error', 'You do not have access to that page.');
            redirect('login.php');
        }
    }

    public static function requireTeacher(): void
    {
        self::requireAuth();

        if (! self::isTeacher()) {
            self::logout();
            flash('error', 'You do not have access to that page.');
            redirect('login.php');
        }

        if ((self::user()['staff_id'] ?? null) === null) {
            self::logout();
            flash('error', 'Teacher accounts must be linked to a staff profile.');
            redirect('login.php');
        }
    }

    public static function requireGuest(): void
    {
        if (! self::check()) {
            return;
        }

        redirect(self::isAdmin() ? 'admin/dashboard.php' : 'teacher/dashboard.php');  
    }

    public static function isAdmin(): bool
    {
        return (self::user()['role'] ?? null) === 'admin';
    }

    public static function isTeacher(): bool
    {
        return (self::user()['role'] ?? null) === 'teacher';
    }

    public static function logout(): void
    {
        unset($_SESSION['auth_user']);
        session_regenerate_id(true);
    }
}

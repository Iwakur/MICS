<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use RuntimeException;

final class DatabaseProvisioner
{
    private static bool $initialized = false;

    public static function ensureReady(): void
    {
        if (self::$initialized) {
            return;
        }

        self::assertExtensionLoaded();

        if (! self::applicationSchemaExists()) {
            self::provision();
            self::$initialized = true;

            return;
        }

        self::applySchema();
        self::ensureUploadDirectoryExists();
        self::$initialized = true;
    }

    public static function provision(): void
    {
        self::assertExtensionLoaded();

        $database = config('database');
        $serverPdo = self::connectToDatabase('postgres');

        $statement = $serverPdo->prepare('SELECT 1 FROM pg_database WHERE datname = :name');
        $statement->execute(['name' => $database['name']]);

        if ($statement->fetchColumn() === false) {
            $serverPdo->exec(sprintf('CREATE DATABASE "%s"', str_replace('"', '""', $database['name'])));
        }

        self::applyDatabaseTimezoneDefault($serverPdo, $database['name']);

        $appPdo = self::connectToDatabase($database['name']);
        self::applySchema($appPdo);
        self::seedBaseData($appPdo);
        self::ensureUploadDirectoryExists();
        self::$initialized = true;
    }

    public static function rebuildPublicSchema(): void
    {
        self::assertExtensionLoaded();

        $database = config('database');
        $serverPdo = self::connectToDatabase('postgres');

        $statement = $serverPdo->prepare('SELECT 1 FROM pg_database WHERE datname = :name');
        $statement->execute(['name' => $database['name']]);

        if ($statement->fetchColumn() === false) {
            $serverPdo->exec(sprintf('CREATE DATABASE "%s"', str_replace('"', '""', $database['name'])));
        }

        self::applyDatabaseTimezoneDefault($serverPdo, $database['name']);

        $appPdo = self::connectToDatabase($database['name']);
        $appPdo->exec('DROP SCHEMA IF EXISTS public CASCADE');
        $appPdo->exec('CREATE SCHEMA public');
        $appPdo->exec('GRANT ALL ON SCHEMA public TO public');

        self::applySchema($appPdo);
        self::seedBaseData($appPdo);
        self::ensureUploadDirectoryExists();
        self::$initialized = true;
    }

    private static function applicationSchemaExists(): bool
    {
        try {
            $database = config('database');
            $pdo = self::connectToDatabase($database['name']);
            $statement = $pdo->query("SELECT to_regclass('public.users')");
            $usersTable = $statement !== false ? $statement->fetchColumn() : false;

            return $usersTable !== false && $usersTable !== null;
        } catch (PDOException) {
            return false;
        }
    }

    private static function connectToDatabase(string $databaseName): PDO
    {
        $database = config('database');
        $dsn = self::buildPgsqlDsn($database['host'], $database['port'], $databaseName, $database['charset'] ?? null);

        $connection = new PDO($dsn, $database['user'], $database['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        self::applySessionSettings($connection);

        return $connection;
    }

    private static function buildPgsqlDsn(string $host, string $port, string $databaseName, ?string $charset): string
    {
        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $databaseName);

        if (is_string($charset) && $charset !== '') {
            $dsn .= sprintf(";options='--client_encoding=%s'", str_replace("'", '', $charset));
        }

        return $dsn;
    }

    private static function assertExtensionLoaded(): void
    {
        if (! extension_loaded('pdo_pgsql')) {
            throw new RuntimeException(
                'The current PHP runtime does not have pdo_pgsql enabled. Enable pdo_pgsql in php.ini before continuing.'
            );
        }
    }

    private static function applyDatabaseTimezoneDefault(PDO $serverPdo, string $databaseName): void
    {
        $appTimezone = app_timezone();

        if ($appTimezone === '') {
            return;
        }

        $serverPdo->exec(sprintf(
            'ALTER DATABASE "%s" SET timezone TO %s',
            str_replace('"', '""', $databaseName),
            $serverPdo->quote($appTimezone)
        ));
    }

    private static function applySessionSettings(PDO $connection): void
    {
        $appTimezone = app_timezone();

        if ($appTimezone !== '') {
            $connection->exec(sprintf('SET TIME ZONE %s', $connection->quote($appTimezone)));
        }
    }

    private static function ensureUploadDirectoryExists(): void
    {
        foreach ([
            base_path('uploads/profiles'),
            base_path('uploads/statements'),
        ] as $directory) {
            if (is_dir($directory)) {
                continue;
            }

            if (! mkdir($directory, 0777, true) && ! is_dir($directory)) {
                throw new RuntimeException(sprintf('Failed to create %s directory.', $directory));
            }
        }
    }

    private static function applySchema(?PDO $pdo = null): void
    {
        $schemaSql = file_get_contents(base_path('database/schema.sql'));

        if ($schemaSql === false) {
            throw new RuntimeException('Failed to read database/schema.sql');
        }

        ($pdo ?? self::connectToDatabase((string) config('database.name')))->exec($schemaSql);
    }

    private static function seedBaseData(PDO $pdo): void
    {
        $adminStaffId = self::findStaffIdByEmail($pdo, 'admin@example.com');

        if ($adminStaffId === null) {
            $statement = $pdo->prepare(
                'INSERT INTO staff (role, first_name, family_name, father_name, status, email, created_at, updated_at)
                 VALUES (:role, :first_name, :family_name, :father_name, :status, :email, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                 RETURNING id'
            );

            $statement->execute([
                'role' => 'Administrator',
                'first_name' => 'Administrator',
                'family_name' => 'System',
                'father_name' => null,
                'status' => 'active',
                'email' => 'admin@example.com',
            ]);

            $adminStaffId = (int) $statement->fetchColumn();
        }

        self::seedUser($pdo, 'admin', default_user_password(), 'admin', $adminStaffId);
    }

    private static function seedUser(PDO $pdo, string $username, string $password, string $role, ?int $staffId): void
    {
        $statement = $pdo->prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
        $statement->execute(['username' => $username]);

        $userId = $statement->fetchColumn();

        if ($userId !== false) {
            $update = $pdo->prepare(
                'UPDATE users
                 SET staff_id = :staff_id,
                     role = :role,
                     is_active = TRUE,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );

            $update->execute([
                'id' => $userId,
                'staff_id' => $staffId,
                'role' => $role,
            ]);

            return;
        }

        $insert = $pdo->prepare(
            'INSERT INTO users (staff_id, username, password_hash, role, is_active, created_at, updated_at)
             VALUES (:staff_id, :username, :password_hash, :role, TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
        );

        $insert->execute([
            'staff_id' => $staffId,
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);
    }

    private static function findStaffIdByEmail(PDO $pdo, string $email): ?int
    {
        $statement = $pdo->prepare('SELECT id FROM staff WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $value = $statement->fetchColumn();

        return $value === false ? null : (int) $value;
    }
}

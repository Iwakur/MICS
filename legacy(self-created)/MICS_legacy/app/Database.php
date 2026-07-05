<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $config = config('database');
        $dsn = self::buildDsn($config['host'], $config['port'], $config['name'], $config['charset'] ?? null);

        if (! extension_loaded('pdo_pgsql')) {
            throw new RuntimeException(
                'Database connection failed: the pdo_pgsql extension is not enabled for the current PHP runtime.'
            );
        }

        try {
            self::$connection = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            self::applySessionSettings(self::$connection);
        } catch (PDOException $exception) {
            throw new RuntimeException('Database connection failed: '.$exception->getMessage(), 0, $exception);
        }

        return self::$connection;
    }

    private static function buildDsn(string $host, string $port, string $database, ?string $charset): string
    {
        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $database);

        if (is_string($charset) && $charset !== '') {
            $dsn .= sprintf(";options='--client_encoding=%s'", str_replace("'", '', $charset));
        }

        return $dsn;
    }

    private static function applySessionSettings(PDO $connection): void
    {
        $appTimezone = app_timezone();

        if ($appTimezone !== '') {
            $connection->exec(sprintf('SET TIME ZONE %s', $connection->quote($appTimezone)));
        }
    }

    public static function tableExists(string $table): bool
    {
        $sql = "SELECT to_regclass('public.' || :table)";
        $statement = self::connection()->prepare($sql);
        $statement->execute(['table' => $table]);
        $value = $statement->fetchColumn();

        return $value !== false && $value !== null;
    }
}

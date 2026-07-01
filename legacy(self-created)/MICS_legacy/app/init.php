<?php

declare(strict_types=1);

if (! defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

require_once BASE_PATH . '/app/helpers.php';

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (! str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

date_default_timezone_set((string) config('app.timezone'));

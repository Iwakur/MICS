<?php

declare(strict_types=1);

require __DIR__ . '/init.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

\App\DatabaseProvisioner::ensureReady();
(new \App\Services\RecurringFinanceService())->ensureCurrentMonthDocuments();

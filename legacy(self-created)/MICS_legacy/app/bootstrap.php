<?php

declare(strict_types=1);
use App\DatabaseProvisioner;
use App\Services\RecurringFinanceService;

require __DIR__.'/init.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

DatabaseProvisioner::ensureReady();
(new RecurringFinanceService)->ensureCurrentMonthDocuments();

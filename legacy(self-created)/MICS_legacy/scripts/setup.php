<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/init.php';

\App\DatabaseProvisioner::provision();

$database = config('database');

echo "Setup complete.\n";
echo "Database: {$database['name']}\n";
echo "Admin login: admin / ChangeMe123!\n";

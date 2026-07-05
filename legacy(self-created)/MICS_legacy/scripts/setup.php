<?php

declare(strict_types=1);
use App\DatabaseProvisioner;

require dirname(__DIR__).'/app/init.php';

DatabaseProvisioner::provision();

$database = config('database');

echo "Setup complete.\n";
echo "Database: {$database['name']}\n";
echo "Admin login: admin / ChangeMe123!\n";

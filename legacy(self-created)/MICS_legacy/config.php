<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'MICS Hub',
        'base_path' => '/MICS',
        'timezone' => 'Europe/Kyiv',
    ],

    'auth' => [
        'default_password' => 'Mics1234',
    ],

    'database' => [
        'host' => '127.0.0.1',
        'port' => '5432',
        'name' => 'mics',
        'user' => 'postgres',
        'pass' => 'toor',
        'charset' => 'UTF8',
    ],
];

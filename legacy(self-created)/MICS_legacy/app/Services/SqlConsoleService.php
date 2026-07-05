<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SqlConsoleRepository;

final class SqlConsoleService
{
    public function __construct(private readonly SqlConsoleRepository $repository) {}

    public function validate(array $input): array
    {
        $sql = trim((string) ($input['sql'] ?? ''));
        $errors = [];

        if ($sql === '') {
            $errors['sql'] = 'SQL is required.';
        }

        return [
            'sql' => $sql,
            'errors' => $errors,
        ];
    }

    public function execute(string $sql): array
    {
        return $this->repository->execute($sql);
    }
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;
use PDOStatement;

final class SqlConsoleRepository
{
    public function execute(string $sql): array
    {
        $statement = Database::connection()->prepare($sql);
        $statement->execute();

        if ($statement->columnCount() > 0) {
            $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

            return [
                'type' => 'result_set',
                'columns' => $this->resolveColumns($statement, $rows),
                'rows' => $rows,
                'row_count' => count($rows),
                'message' => sprintf('Query executed. %d row(s) returned.', count($rows)),
            ];
        }

        $affectedRows = $statement->rowCount();

        return [
            'type' => 'statement',
            'columns' => [],
            'rows' => [],
            'row_count' => $affectedRows,
            'message' => $affectedRows > 0
                ? sprintf('Statement executed successfully. %d row(s) affected.', $affectedRows)
                : 'Statement executed successfully.',
        ];
    }

    private function resolveColumns(PDOStatement $statement, array $rows): array
    {
        if ($rows !== []) {
            return array_map('strval', array_keys($rows[0]));
        }

        $columns = [];

        for ($index = 0; $index < $statement->columnCount(); $index++) {
            $meta = $statement->getColumnMeta($index);
            $columns[] = is_array($meta) && isset($meta['name']) ? (string) $meta['name'] : 'column_'.($index + 1);
        }

        return $columns;
    }
}

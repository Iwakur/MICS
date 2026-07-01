<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Database;
use PDO;

final class JournalRepository
{
    public function findBySource(string $sourceType, int $sourceId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, entry_date, reference, description, source_type, source_id, reversal_of_journal_entry_id, status, created_at
             FROM journal_entries
             WHERE source_type = :source_type AND source_id = :source_id
             LIMIT 1'
        );

        $statement->execute([
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);

        $entry = $statement->fetch(PDO::FETCH_ASSOC);

        if (! is_array($entry)) {
            return null;
        }

        $entry['lines'] = $this->findLinesForEntry((int) $entry['id']);

        return $entry;
    }

    public function findForStudent(int $studentId): array
    {
        return $this->findEntriesByDimension('student_id', $studentId);
    }

    public function findForStaff(int $staffId): array
    {
        return $this->findEntriesByDimension('staff_id', $staffId);
    }

    private function findEntriesByDimension(string $dimension, int $entityId): array
    {
        $allowedDimensions = ['student_id', 'staff_id'];
        if (! in_array($dimension, $allowedDimensions, true)) {
            throw new \InvalidArgumentException('Unsupported journal dimension filter.');
        }

        $sql = sprintf(
            'SELECT DISTINCT je.id, je.entry_date, je.reference, je.description, je.source_type, je.source_id, je.reversal_of_journal_entry_id, je.status, je.created_at
             FROM journal_entries je
             INNER JOIN journal_entry_lines jel ON jel.journal_entry_id = je.id
             WHERE jel.%s = :entity_id
             ORDER BY je.entry_date DESC, je.id DESC',
            $dimension
        );

        $statement = Database::connection()->prepare($sql);
        $statement->execute(['entity_id' => $entityId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function findLinesForEntry(int $journalEntryId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, journal_entry_id, account_id, debit_amount, credit_amount, student_id, staff_id, note
             FROM journal_entry_lines
             WHERE journal_entry_id = :journal_entry_id
             ORDER BY id ASC'
        );

        $statement->execute(['journal_entry_id' => $journalEntryId]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}

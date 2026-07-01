<?php

declare(strict_types=1);

namespace App\Services;

use App\Database;
use InvalidArgumentException;
use PDO;
use Throwable;

final class AccountingService
{
    private const DIMENSION_RULES = [
        'student_charge' => ['student_id', 'staff_id'],
        'payment' => ['student_id'],
        'staff_payout' => ['staff_id'],
    ];

    public function postBalancedEntry(array $entry, array $lines): int
    {
        $this->validateEntry($entry, $lines);
        $this->validateLines($lines);

        $pdo = Database::connection();

        try {
            $pdo->beginTransaction();

            $statement = $pdo->prepare(
                'INSERT INTO journal_entries (entry_date, reference, description, source_type, source_id, reversal_of_journal_entry_id, status, created_at)
                 VALUES (:entry_date, :reference, :description, :source_type, :source_id, :reversal_of_journal_entry_id, :status, CURRENT_TIMESTAMP)
                 RETURNING id'
            );

            $statement->execute([
                'entry_date' => $entry['entry_date'],
                'reference' => $entry['reference'] ?? null,
                'description' => $entry['description'] ?? null,
                'source_type' => $entry['source_type'],
                'source_id' => $entry['source_id'] ?? null,
                'reversal_of_journal_entry_id' => $entry['reversal_of_journal_entry_id'] ?? null,
                'status' => $entry['status'] ?? 'posted',
            ]);

            $journalEntryId = (int) $statement->fetchColumn();

            $lineStatement = $pdo->prepare(
                'INSERT INTO journal_entry_lines (journal_entry_id, account_id, debit_amount, credit_amount, student_id, staff_id, note)
                 VALUES (:journal_entry_id, :account_id, :debit_amount, :credit_amount, :student_id, :staff_id, :note)'
            );

            foreach ($lines as $line) {
                $lineStatement->execute([
                    'journal_entry_id' => $journalEntryId,
                    'account_id' => $line['account_id'],
                    'debit_amount' => $line['debit_amount'],
                    'credit_amount' => $line['credit_amount'],
                    'student_id' => $line['student_id'] ?? null,
                    'staff_id' => $line['staff_id'] ?? null,
                    'note' => $line['note'] ?? null,
                ]);
            }

            $pdo->commit();

            return $journalEntryId;
        } catch (Throwable $throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $throwable;
        }
    }

    public function findJournalEntryIdForSource(string $sourceType, int $sourceId): ?int
    {
        $statement = Database::connection()->prepare(
            'SELECT id
             FROM journal_entries
             WHERE source_type = :source_type AND source_id = :source_id
             LIMIT 1'
        );

        $statement->execute([
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);

        $value = $statement->fetchColumn();

        return $value === false ? null : (int) $value;
    }

    private function validateEntry(array $entry, array $lines): void
    {
        $sourceType = (string) ($entry['source_type'] ?? '');
        $sourceId = $entry['source_id'] ?? null;
        $reversalOfJournalEntryId = $entry['reversal_of_journal_entry_id'] ?? null;
        $status = (string) ($entry['status'] ?? 'posted');

        if ($sourceType === '') {
            throw new InvalidArgumentException('Journal entry source_type is required.');
        }

        if (! in_array($status, ['draft', 'posted', 'void'], true)) {
            throw new InvalidArgumentException('Journal entry status must be draft, posted, or void.');
        }

        if (in_array($sourceType, ['student_charge', 'payment', 'expense', 'staff_payout'], true) && ! is_numeric($sourceId)) {
            throw new InvalidArgumentException('Business-linked journal entries must include source_id.');
        }

        if (is_numeric($sourceId) && $this->findJournalEntryIdForSource($sourceType, (int) $sourceId) !== null) {
            throw new InvalidArgumentException('This source document is already linked to a journal entry.');
        }

        if ($sourceType === 'reversal' && ! is_numeric($reversalOfJournalEntryId)) {
            throw new InvalidArgumentException('Reversal entries must point to reversal_of_journal_entry_id.');
        }

        if ($sourceType !== 'reversal' && $reversalOfJournalEntryId !== null) {
            throw new InvalidArgumentException('Only reversal entries may set reversal_of_journal_entry_id.');
        }

        if (is_numeric($reversalOfJournalEntryId) && ! $this->journalEntryExists((int) $reversalOfJournalEntryId)) {
            throw new InvalidArgumentException('reversal_of_journal_entry_id must point to an existing journal entry.');
        }

        if ($status === 'posted') {
            $this->validateRequiredDimensions($sourceType, $lines);
        }
    }

    private function validateRequiredDimensions(string $sourceType, array $lines): void
    {
        if (! array_key_exists($sourceType, self::DIMENSION_RULES)) {
            return;
        }

        foreach (self::DIMENSION_RULES[$sourceType] as $dimension) {
            if (! $this->hasAnyDimensionValue($lines, $dimension)) {
                throw new InvalidArgumentException(sprintf(
                    'Posted %s entries must include %s in at least one journal line.',
                    $sourceType,
                    $dimension
                ));
            }
        }
    }

    private function hasAnyDimensionValue(array $lines, string $dimension): bool
    {
        foreach ($lines as $line) {
            if (isset($line[$dimension]) && $line[$dimension] !== null && $line[$dimension] !== '') {
                return true;
            }
        }

        return false;
    }

    private function journalEntryExists(int $journalEntryId): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT 1
             FROM journal_entries
             WHERE id = :id
             LIMIT 1'
        );

        $statement->execute(['id' => $journalEntryId]);

        return $statement->fetchColumn() !== false;
    }

    private function validateLines(array $lines): void
    {
        if (count($lines) < 2) {
            throw new InvalidArgumentException('A journal entry must contain at least two lines.');
        }

        $debits = 0.0;
        $credits = 0.0;

        foreach ($lines as $line) {
            $debit = (float) ($line['debit_amount'] ?? 0);
            $credit = (float) ($line['credit_amount'] ?? 0);

            if ($debit < 0 || $credit < 0) {
                throw new InvalidArgumentException('Debit and credit amounts must be non-negative.');
            }

            if (($debit > 0 && $credit > 0) || ($debit == 0.0 && $credit == 0.0)) {
                throw new InvalidArgumentException('Each line must have exactly one side filled.');
            }

            $debits += $debit;
            $credits += $credit;
        }

        if (round($debits, 2) !== round($credits, 2)) {
            throw new InvalidArgumentException('Journal entry is not balanced.');
        }
    }
}

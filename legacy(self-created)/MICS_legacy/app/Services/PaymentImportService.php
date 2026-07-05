<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\PaymentImportRepository;
use InvalidArgumentException;
use RuntimeException;

final class PaymentImportService
{
    public const ROW_STATUSES = ['new', 'draft_created', 'unmatched', 'ignored', 'deleted'];

    private const EXPECTED_HEADERS = [
        'ЄДРПОУ',
        'МФО',
        'Рахунок',
        'Валюта',
        'Номер документу',
        'Дата операції',
        'МФО банку',
        'Назва банку',
        'Рахунок кореспондента',
        'ЄДРПОУ кореспондента',
        'Кореспондент',
        'Сума',
        'Призначення платежу',
    ];

    public function __construct(
        private readonly PaymentImportRepository $repository = new PaymentImportRepository
    ) {}

    public function importUploadedStatement(array $file, int $userId): array
    {
        $tmpPath = $this->validateUpload($file);
        $fileHash = hash_file('sha256', $tmpPath);

        if ($fileHash === false) {
            throw new RuntimeException('Unable to hash uploaded file.');
        }

        $existingBatch = $this->repository->findBatchByFileHash($fileHash);
        if ($existingBatch !== null) {
            return [
                'created' => false,
                'batch_id' => (int) $existingBatch['id'],
                'message' => 'This statement file was already imported earlier.',
            ];
        }

        $content = file_get_contents($tmpPath);
        if ($content === false) {
            throw new RuntimeException('Unable to read uploaded statement file.');
        }

        $utf8Content = $this->convertToUtf8($content);
        $lines = preg_split('/\r\n|\r|\n/', $utf8Content) ?: [];
        $lines = array_values(array_filter($lines, static fn (string $line): bool => trim($line) !== ''));

        if ($lines === []) {
            throw new InvalidArgumentException('The uploaded CSV file is empty.');
        }

        $header = $this->parseCsvLine(array_shift($lines));
        if ($header !== self::EXPECTED_HEADERS) {
            throw new InvalidArgumentException('Unexpected CSV headers. Upload the bank export in its original format.');
        }

        $students = $this->repository->studentDirectory();
        $rows = [];

        foreach ($lines as $index => $line) {
            $parsed = $this->parseCsvLine($line);

            if (count($parsed) !== count(self::EXPECTED_HEADERS)) {
                continue;
            }

            $row = $this->normalizeRow($parsed, $index + 2);
            $suggestedStudentIds = $this->suggestStudentIds($row, $students);
            $row['suggested_student_ids'] = json_encode($suggestedStudentIds, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $row['status'] = $row['direction'] === 'incoming' && $suggestedStudentIds === [] ? 'unmatched' : 'new';
            $row['raw_payload'] = json_encode(array_combine(self::EXPECTED_HEADERS, $parsed), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $row['row_hash'] = $this->rowHash($row);
            $rows[] = $row;
        }

        if ($rows === []) {
            throw new InvalidArgumentException('No valid statement rows were found in the uploaded CSV.');
        }

        $storedFilename = $this->storeUploadedFile($tmpPath, (string) ($file['name'] ?? 'statement.csv'), $fileHash);
        $accountNumber = $rows[0]['account_number'] ?? null;
        $currency = $rows[0]['currency'] ?? null;

        $importResult = $this->repository->createBatchWithRows([
            'original_filename' => (string) ($file['name'] ?? 'statement.csv'),
            'stored_filename' => $storedFilename,
            'file_hash' => $fileHash,
            'account_number' => $accountNumber,
            'currency' => $currency,
            'imported_by_user_id' => $userId,
            'total_rows' => count($rows),
            'new_rows' => count($rows),
            'duplicate_rows' => 0,
        ], $rows);

        return [
            'created' => true,
            'batch_id' => (int) $importResult['batch_id'],
            'message' => sprintf(
                'Statement imported. %d new rows added, %d duplicates skipped.',
                (int) $importResult['new_rows'],
                (int) $importResult['duplicate_rows']
            ),
        ];
    }

    public function decodeSuggestedStudentIds(?string $json): array
    {
        if (! is_string($json) || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? array_values(array_filter(array_map('intval', $decoded), static fn (int $id): bool => $id > 0)) : [];
    }

    public function normalizeCoveredMonth(string $value): string
    {
        $value = trim($value);

        if (! preg_match('/^\d{4}-\d{2}$/', $value)) {
            throw new InvalidArgumentException('Covered month must be selected.');
        }

        return $value.'-01';
    }

    public function statusLabel(string $status): string
    {
        return str_replace('_', ' ', ucfirst($status));
    }

    private function validateUpload(array $file): string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('The statement upload failed. Please choose the CSV file again.');
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || ! is_uploaded_file($tmpPath)) {
            throw new InvalidArgumentException('Invalid uploaded statement file.');
        }

        return $tmpPath;
    }

    private function convertToUtf8(string $content): string
    {
        $converted = @iconv('Windows-1251', 'UTF-8//IGNORE', $content);

        if ($converted === false) {
            throw new InvalidArgumentException('Unable to decode the CSV file from Windows-1251.');
        }

        return $converted;
    }

    private function parseCsvLine(string $line): array
    {
        $values = str_getcsv($line, ';');

        if ($values !== [] && end($values) === '') {
            array_pop($values);
        }

        return array_map(static fn (string $value): string => trim($value), $values);
    }

    private function normalizeRow(array $parsed, int $rowNumber): array
    {
        $amount = (float) str_replace([' ', ','], ['', '.'], $parsed[11]);
        $operationDate = \DateTimeImmutable::createFromFormat('d.m.Y', $parsed[5]);

        if (! $operationDate instanceof \DateTimeImmutable) {
            throw new InvalidArgumentException(sprintf('Invalid operation date on row %d.', $rowNumber));
        }

        return [
            'row_number' => $rowNumber,
            'owner_edrpou' => $parsed[0] !== '' ? $parsed[0] : null,
            'owner_mfo' => $parsed[1] !== '' ? $parsed[1] : null,
            'account_number' => $parsed[2] !== '' ? $parsed[2] : null,
            'currency' => $parsed[3] !== '' ? $parsed[3] : null,
            'document_number' => $parsed[4] !== '' ? $parsed[4] : null,
            'operation_date' => $operationDate->format('Y-m-d'),
            'bank_mfo' => $parsed[6] !== '' ? $parsed[6] : null,
            'bank_name' => $parsed[7] !== '' ? $parsed[7] : null,
            'correspondent_account' => $parsed[8] !== '' ? $parsed[8] : null,
            'correspondent_edrpou' => $parsed[9] !== '' ? $parsed[9] : null,
            'correspondent_name' => $parsed[10] !== '' ? $parsed[10] : null,
            'amount' => abs($amount),
            'payment_purpose' => $parsed[12] !== '' ? $parsed[12] : null,
            'direction' => $amount >= 0 ? 'incoming' : 'outgoing',
        ];
    }

    private function suggestStudentIds(array $row, array $students): array
    {
        $haystack = $this->normalizeText(trim((string) ($row['correspondent_name'] ?? '').' '.(string) ($row['payment_purpose'] ?? '')));
        if ($haystack === '') {
            return [];
        }

        $scored = [];

        foreach ($students as $student) {
            $fullName = person_full_name(
                isset($student['family_name']) ? (string) $student['family_name'] : null,
                isset($student['first_name']) ? (string) $student['first_name'] : null,
                isset($student['father_name']) ? (string) $student['father_name'] : null
            );
            $normalizedName = $this->normalizeText($fullName);
            if ($normalizedName === '') {
                continue;
            }

            $score = 0;
            foreach ($this->meaningfulTokens($normalizedName) as $token) {
                if (str_contains($haystack, $token)) {
                    $score += strlen($token) >= 5 ? 3 : 1;
                }
            }

            if ($score > 0) {
                if ((string) ($student['status'] ?? '') === 'archived') {
                    $score -= 1;
                }

                $scored[] = [
                    'id' => (int) $student['id'],
                    'score' => $score,
                ];
            }
        }

        usort($scored, static function (array $left, array $right): int {
            if ($left['score'] === $right['score']) {
                return $left['id'] <=> $right['id'];
            }

            return $right['score'] <=> $left['score'];
        });

        return array_column(array_slice($scored, 0, 3), 'id');
    }

    private function meaningfulTokens(string $text): array
    {
        $parts = preg_split('/\s+/u', $text) ?: [];
        $tokens = [];

        foreach ($parts as $part) {
            $clean = preg_replace('/[^\p{L}\p{N}]+/u', '', $part) ?? '';
            $length = function_exists('mb_strlen') ? mb_strlen($clean, 'UTF-8') : strlen($clean);
            if ($clean === '' || $length < 2) {
                continue;
            }

            $tokens[$clean] = true;
        }

        return array_keys($tokens);
    }

    private function normalizeText(string $value): string
    {
        $value = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/u', ' ', $value) ?? '');
    }

    private function rowHash(array $row): string
    {
        return hash('sha256', json_encode([
            'account_number' => $row['account_number'],
            'document_number' => $row['document_number'],
            'operation_date' => $row['operation_date'],
            'correspondent_account' => $row['correspondent_account'],
            'correspondent_name' => $row['correspondent_name'],
            'amount' => number_format((float) $row['amount'], 2, '.', ''),
            'payment_purpose' => $row['payment_purpose'],
            'direction' => $row['direction'],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    private function storeUploadedFile(string $tmpPath, string $originalName, string $fileHash): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $extension = $extension !== '' ? $extension : 'csv';
        $filename = date('Ymd_His').'_'.substr($fileHash, 0, 12).'.'.$extension;
        $destination = base_path('uploads/statements/'.$filename);

        if (! move_uploaded_file($tmpPath, $destination)) {
            throw new RuntimeException('Failed to store the uploaded statement file.');
        }

        return $filename;
    }
}

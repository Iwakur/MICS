<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;

final class PaymentFormService
{
    public const STATUSES = ['pending', 'confirmed', 'void'];

    public function validate(array $input, array $studentIds): array
    {
        $errors = [];
        $studentId = (int) ($input['student_id'] ?? 0);
        $paymentDateRaw = trim((string) ($input['payment_date'] ?? ''));
        $amountRaw = trim((string) ($input['amount'] ?? ''));
        $method = trim((string) ($input['method'] ?? ''));
        $source = trim((string) ($input['source'] ?? ''));
        $externalReference = trim((string) ($input['external_reference'] ?? ''));
        $status = trim((string) ($input['status'] ?? 'pending'));
        $coveredMonthRaw = trim((string) ($input['covered_month'] ?? ''));
        $comment = trim((string) ($input['comment'] ?? ''));

        if (! in_array($studentId, $studentIds, true)) {
            $errors['student_id'] = 'Choose a valid student.';
        }

        $paymentDate = $this->normalizeDateTimeLocal($paymentDateRaw);
        if ($paymentDate === null) {
            $errors['payment_date'] = 'Enter a valid payment date and time.';
        }

        if (! is_numeric($amountRaw) || (float) $amountRaw <= 0) {
            $errors['amount'] = 'Amount must be greater than zero.';
        }

        if ($method === '') {
            $errors['method'] = 'Method is required.';
        }

        if ($source === '') {
            $errors['source'] = 'Source is required.';
        }

        if (! in_array($status, self::STATUSES, true)) {
            $errors['status'] = 'Choose a valid payment status.';
        }

        $coveredMonth = $this->normalizeCoveredMonth($coveredMonthRaw);
        if ($coveredMonthRaw !== '' && $coveredMonth === null) {
            $errors['covered_month'] = 'Choose a valid covered month.';
        }

        return [
            'errors' => $errors,
            'data' => [
                'student_id' => $studentId,
                'payment_date' => $paymentDate,
                'amount' => round((float) $amountRaw, 2),
                'method' => $method,
                'source' => $source,
                'external_reference' => $externalReference !== '' ? $externalReference : null,
                'status' => $status,
                'covered_month' => $coveredMonth,
                'import_row_id' => null,
                'comment' => $comment !== '' ? $comment : null,
                'confirmed_at' => $status === 'confirmed' ? current_app_datetime()->format('Y-m-d H:i:sP') : null,
            ],
        ];
    }

    public function defaults(?array $payment = null): array
    {
        return [
            'student_id' => (string) old('student_id', (string) ($payment['student_id'] ?? '')),
            'payment_date' => (string) old('payment_date', $this->formatDateTimeLocal($payment['payment_date'] ?? null) ?? ''),
            'amount' => (string) old('amount', isset($payment['amount']) ? (string) $payment['amount'] : ''),
            'method' => (string) old('method', (string) ($payment['method'] ?? 'bank_transfer')),
            'source' => (string) old('source', (string) ($payment['source'] ?? 'manual')),
            'external_reference' => (string) old('external_reference', (string) ($payment['external_reference'] ?? '')),
            'status' => (string) old('status', (string) ($payment['status'] ?? 'pending')),
            'covered_month' => (string) old('covered_month', $this->formatMonthInput($payment['covered_month'] ?? null) ?? ''),
            'comment' => (string) old('comment', (string) ($payment['comment'] ?? '')),
        ];
    }

    private function normalizeDateTimeLocal(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value)
            ?: DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $value);

        return $dateTime instanceof DateTimeImmutable ? $dateTime->format('Y-m-d H:i:s') : null;
    }

    private function normalizeCoveredMonth(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        return preg_match('/^\d{4}-\d{2}$/', $value) === 1 ? $value . '-01' : null;
    }

    private function formatDateTimeLocal(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return (new DateTimeImmutable($value))->format('Y-m-d\TH:i');
        } catch (\Exception) {
            return null;
        }
    }

    private function formatMonthInput(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return (new DateTimeImmutable($value))->format('Y-m');
        } catch (\Exception) {
            return null;
        }
    }
}

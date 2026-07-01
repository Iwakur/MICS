<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;

final class PayoutFormService
{
    public function validate(array $input, array $staffIds): array
    {
        $errors = [];
        $staffId = (int) ($input['staff_id'] ?? 0);
        $payoutDateRaw = trim((string) ($input['payout_date'] ?? ''));
        $amountRaw = trim((string) ($input['amount'] ?? ''));
        $comment = trim((string) ($input['comment'] ?? ''));

        if (! in_array($staffId, $staffIds, true)) {
            $errors['staff_id'] = 'Choose a valid staff member.';
        }

        $payoutDate = $this->normalizeDateTimeLocal($payoutDateRaw);
        if ($payoutDate === null) {
            $errors['payout_date'] = 'Enter a valid payout date and time.';
        }

        if (! is_numeric($amountRaw) || (float) $amountRaw <= 0) {
            $errors['amount'] = 'Amount must be greater than zero.';
        }

        return [
            'errors' => $errors,
            'data' => [
                'staff_id' => $staffId,
                'payout_date' => $payoutDate,
                'amount' => round((float) $amountRaw, 2),
                'comment' => $comment !== '' ? $comment : null,
            ],
        ];
    }

    public function defaults(?array $payout = null): array
    {
        return [
            'staff_id' => (string) old('staff_id', (string) ($payout['staff_id'] ?? '')),
            'payout_date' => (string) old('payout_date', $this->formatDateTimeLocal($payout['payout_date'] ?? null) ?? ''),
            'amount' => (string) old('amount', isset($payout['amount']) ? (string) $payout['amount'] : ''),
            'comment' => (string) old('comment', (string) ($payout['comment'] ?? '')),
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
}

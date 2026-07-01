<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;

final class ExpenseFormService
{
    public const STATUSES = ['draft', 'posted', 'void'];

    public function validate(array $input, array $categoryIds, array $accountIds, array $staffIds): array
    {
        $errors = [];
        $expenseDateRaw = trim((string) ($input['expense_date'] ?? ''));
        $categoryId = (int) ($input['category_id'] ?? 0);
        $amountRaw = trim((string) ($input['amount'] ?? ''));
        $paidFromAccountId = (int) ($input['paid_from_account_id'] ?? 0);
        $staffIdRaw = (int) ($input['staff_id'] ?? 0);
        $status = trim((string) ($input['status'] ?? 'draft'));
        $description = trim((string) ($input['description'] ?? ''));
        $reason = trim((string) ($input['reason'] ?? ''));

        $expenseDate = $this->normalizeDateTimeLocal($expenseDateRaw);
        if ($expenseDate === null) {
            $errors['expense_date'] = 'Enter a valid expense date and time.';
        }

        if (! in_array($categoryId, $categoryIds, true)) {
            $errors['category_id'] = 'Choose a valid expense category.';
        }

        if (! is_numeric($amountRaw) || (float) $amountRaw <= 0) {
            $errors['amount'] = 'Amount must be greater than zero.';
        }

        if (! in_array($paidFromAccountId, $accountIds, true)) {
            $errors['paid_from_account_id'] = 'Choose a valid paying account.';
        }

        if ($staffIdRaw > 0 && ! in_array($staffIdRaw, $staffIds, true)) {
            $errors['staff_id'] = 'Choose a valid staff member.';
        }

        if (! in_array($status, self::STATUSES, true)) {
            $errors['status'] = 'Choose a valid expense status.';
        }

        return [
            'errors' => $errors,
            'data' => [
                'expense_date' => $expenseDate,
                'category_id' => $categoryId,
                'amount' => round((float) $amountRaw, 2),
                'paid_from_account_id' => $paidFromAccountId,
                'staff_id' => $staffIdRaw > 0 ? $staffIdRaw : null,
                'status' => $status,
                'description' => $description !== '' ? $description : null,
                'reason' => $reason !== '' ? $reason : null,
                'posted_at' => $status === 'posted' ? current_app_datetime()->format('Y-m-d H:i:sP') : null,
                'import_row_id' => null,
            ],
        ];
    }

    public function defaults(?array $expense = null): array
    {
        return [
            'expense_date' => (string) old('expense_date', $this->formatDateTimeLocal($expense['expense_date'] ?? null) ?? ''),
            'category_id' => (string) old('category_id', (string) ($expense['category_id'] ?? '')),
            'amount' => (string) old('amount', isset($expense['amount']) ? (string) $expense['amount'] : ''),
            'paid_from_account_id' => (string) old('paid_from_account_id', (string) ($expense['paid_from_account_id'] ?? '')),
            'staff_id' => (string) old('staff_id', (string) ($expense['staff_id'] ?? '')),
            'status' => (string) old('status', (string) ($expense['status'] ?? 'draft')),
            'description' => (string) old('description', (string) ($expense['description'] ?? '')),
            'reason' => (string) old('reason', (string) ($expense['reason'] ?? '')),
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

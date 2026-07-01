<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;

final class StudentFormService
{
    public const STATUSES = ['active', 'paused', 'archived'];

    public function validate(array $input, array $planIds, array $staffIds, bool $allowStaffAssignment, ?int $forcedStaffId = null): array
    {
        $errors = [];
        $firstName = trim((string) ($input['first_name'] ?? ''));
        $middleName = trim((string) ($input['family_name'] ?? ''));
        $lastName = trim((string) ($input['father_name'] ?? ''));
        $phone = trim((string) ($input['phone'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $status = trim((string) ($input['status'] ?? 'active'));
        $planId = (int) ($input['plan_id'] ?? 0);
        $staffId = $allowStaffAssignment ? (int) ($input['staff_id'] ?? 0) : (int) $forcedStaffId;
        $discountAmountRaw = trim((string) ($input['discount_amount'] ?? '0'));
        $joinedAtRaw = trim((string) ($input['joined_at'] ?? ''));
        $comments = trim((string) ($input['comments'] ?? ''));

        if ($firstName === '') {
            $errors['first_name'] = 'First name is required.';
        }

        if (! in_array($status, self::STATUSES, true)) {
            $errors['status'] = 'Choose a valid student status.';
        }

        if (! in_array($planId, $planIds, true)) {
            $errors['plan_id'] = 'Choose a valid plan.';
        }

        if (! in_array($staffId, $staffIds, true)) {
            $errors['staff_id'] = 'Choose a valid teacher.';
        }

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if (! is_numeric($discountAmountRaw) || (float) $discountAmountRaw < 0) {
            $errors['discount_amount'] = 'Discount amount must be zero or greater.';
        }

        $joinedAt = $this->normalizeDateTimeLocal($joinedAtRaw);
        if ($joinedAt === null) {
            $errors['joined_at'] = 'Enter a valid joined date and time.';
        }

        return [
            'errors' => $errors,
            'data' => [
                'first_name' => $firstName,
                'family_name' => $middleName !== '' ? $middleName : null,
                'father_name' => $lastName !== '' ? $lastName : null,
                'phone' => $phone !== '' ? $phone : null,
                'email' => $email !== '' ? $email : null,
                'status' => $status,
                'plan_id' => $planId,
                'staff_id' => $staffId,
                'discount_amount' => round((float) $discountAmountRaw, 2),
                'joined_at' => $joinedAt,
                'comments' => $comments !== '' ? $comments : null,
            ],
        ];
    }

    public function defaults(?array $student = null): array
    {
        return [
            'first_name' => (string) old('first_name', (string) ($student['first_name'] ?? '')),
            'family_name' => (string) old('family_name', (string) ($student['family_name'] ?? '')),
            'father_name' => (string) old('father_name', (string) ($student['father_name'] ?? '')),
            'phone' => (string) old('phone', (string) ($student['phone'] ?? '')),
            'email' => (string) old('email', (string) ($student['email'] ?? '')),
            'status' => (string) old('status', (string) ($student['status'] ?? 'active')),
            'plan_id' => (string) old('plan_id', (string) ($student['plan_id'] ?? '')),
            'staff_id' => (string) old('staff_id', (string) ($student['staff_id'] ?? '')),
            'discount_amount' => (string) old('discount_amount', isset($student['discount_amount']) ? (string) $student['discount_amount'] : '0'),
            'joined_at' => (string) old('joined_at', $this->formatDateTimeLocal($student['joined_at'] ?? null) ?? ''),
            'comments' => (string) old('comments', (string) ($student['comments'] ?? '')),
        ];
    }

    private function normalizeDateTimeLocal(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $dateTime = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value)
            ?: DateTimeImmutable::createFromFormat('Y-m-d\TH:i:s', $value);

        if (! $dateTime instanceof DateTimeImmutable) {
            return null;
        }

        return $dateTime->format('Y-m-d H:i:s');
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

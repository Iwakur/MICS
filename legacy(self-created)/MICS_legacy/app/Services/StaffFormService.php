<?php

declare(strict_types=1);

namespace App\Services;

final class StaffFormService
{
    public const STATUSES = ['active', 'archived'];

    public function validate(array $input): array
    {
        $errors = [];
        $role = trim((string) ($input['role'] ?? ''));
        $firstName = trim((string) ($input['first_name'] ?? ''));
        $familyName = trim((string) ($input['family_name'] ?? ''));
        $fatherName = trim((string) ($input['father_name'] ?? ''));
        $status = trim((string) ($input['status'] ?? 'active'));
        $payoutCardNumber = trim((string) ($input['payout_card_number'] ?? ''));
        $fixedSalaryAmountRaw = trim((string) ($input['fixed_salary_amount'] ?? ''));
        $phone = trim((string) ($input['phone'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $comments = trim((string) ($input['comments'] ?? ''));

        if ($role === '') {
            $errors['role'] = 'Role is required.';
        }

        if ($firstName === '') {
            $errors['first_name'] = 'First name is required.';
        }

        if (! in_array($status, self::STATUSES, true)) {
            $errors['status'] = 'Choose a valid staff status.';
        }

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($fixedSalaryAmountRaw !== '' && (! is_numeric($fixedSalaryAmountRaw) || (float) $fixedSalaryAmountRaw < 0)) {
            $errors['fixed_salary_amount'] = 'Fixed salary must be zero or greater.';
        }

        return [
            'errors' => $errors,
            'data' => [
                'role' => $role,
                'first_name' => $firstName,
                'family_name' => $familyName !== '' ? $familyName : null,
                'father_name' => $fatherName !== '' ? $fatherName : null,
                'status' => $status,
                'payout_card_number' => $payoutCardNumber !== '' ? $payoutCardNumber : null,
                'fixed_salary_amount' => $fixedSalaryAmountRaw !== '' ? round((float) $fixedSalaryAmountRaw, 2) : null,
                'phone' => $phone !== '' ? $phone : null,
                'email' => $email !== '' ? $email : null,
                'comments' => $comments !== '' ? $comments : null,
            ],
        ];
    }

    public function defaults(?array $staff = null): array
    {
        return [
            'role' => (string) old('role', (string) ($staff['role'] ?? '')),
            'first_name' => (string) old('first_name', (string) ($staff['first_name'] ?? '')),
            'family_name' => (string) old('family_name', (string) ($staff['family_name'] ?? '')),
            'father_name' => (string) old('father_name', (string) ($staff['father_name'] ?? '')),
            'status' => (string) old('status', (string) ($staff['status'] ?? 'active')),
            'payout_card_number' => (string) old('payout_card_number', (string) ($staff['payout_card_number'] ?? '')),
            'fixed_salary_amount' => (string) old('fixed_salary_amount', isset($staff['fixed_salary_amount']) ? (string) $staff['fixed_salary_amount'] : ''),
            'phone' => (string) old('phone', (string) ($staff['phone'] ?? '')),
            'email' => (string) old('email', (string) ($staff['email'] ?? '')),
            'comments' => (string) old('comments', (string) ($staff['comments'] ?? '')),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

final class UserFormService
{
    public const ROLES = ['admin', 'teacher'];
    public const ACTIVE_OPTIONS = ['1', '0'];

    public function validate(array $input, array $staffIds, bool $usernameExists): array
    {
        $errors = [];
        $staffId = (int) ($input['staff_id'] ?? 0);
        $username = trim((string) ($input['username'] ?? ''));
        $role = trim((string) ($input['role'] ?? 'teacher'));
        $isActive = (string) ($input['is_active'] ?? '1');

        if (! in_array($staffId, $staffIds, true)) {
            $errors['staff_id'] = 'Choose a valid staff member.';
        }

        if ($username === '') {
            $errors['username'] = 'Username is required.';
        } elseif ($usernameExists) {
            $errors['username'] = 'Username must be unique.';
        }

        if (! in_array($role, self::ROLES, true)) {
            $errors['role'] = 'Choose a valid user role.';
        }

        if (! in_array($isActive, self::ACTIVE_OPTIONS, true)) {
            $errors['is_active'] = 'Choose a valid access state.';
        }

        return [
            'errors' => $errors,
            'data' => [
                'staff_id' => $staffId,
                'username' => $username,
                'role' => $role,
                'is_active' => $isActive === '1',
            ],
        ];
    }

    public function defaults(?array $user = null): array
    {
        return [
            'staff_id' => (string) old('staff_id', (string) ($user['staff_id'] ?? '')),
            'username' => (string) old('username', (string) ($user['username'] ?? '')),
            'role' => (string) old('role', (string) ($user['role'] ?? 'teacher')),
            'is_active' => (string) old('is_active', isset($user['is_active']) ? ((bool) $user['is_active'] ? '1' : '0') : '1'),
        ];
    }
}

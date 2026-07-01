<?php

declare(strict_types=1);

namespace App\Services;

final class TeacherPasswordService
{
    public function validateChange(array $input, bool $currentPasswordValid): array
    {
        $errors = [];
        $currentPassword = (string) ($input['current_password'] ?? '');
        $newPassword = (string) ($input['new_password'] ?? '');
        $confirmPassword = (string) ($input['confirm_password'] ?? '');

        if ($currentPassword === '') {
            $errors['current_password'] = 'Current password is required.';
        } elseif (! $currentPasswordValid) {
            $errors['current_password'] = 'Current password is incorrect.';
        }

        if ($newPassword === '') {
            $errors['new_password'] = 'New password is required.';
        } elseif (strlen($newPassword) < 8) {
            $errors['new_password'] = 'New password must be at least 8 characters.';
        }

        if ($confirmPassword === '') {
            $errors['confirm_password'] = 'Please confirm the new password.';
        } elseif ($confirmPassword !== $newPassword) {
            $errors['confirm_password'] = 'New password confirmation does not match.';
        }

        if ($currentPassword !== '' && $newPassword !== '' && $currentPassword === $newPassword) {
            $errors['new_password'] = 'New password must be different from the current password.';
        }

        return [
            'errors' => $errors,
            'data' => [
                'new_password' => $newPassword,
            ],
        ];
    }
}

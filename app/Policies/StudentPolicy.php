<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

/**
 * Centralizes record-level access rules for student data.
 */
class StudentPolicy
{
    public function update(User $user, Student $student): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $staff = $user->staffMember;

        return $staff !== null
            && $staff->is_active
            && $staff->role?->can_teach
            && $student->staff_id === $staff->id;
    }
}

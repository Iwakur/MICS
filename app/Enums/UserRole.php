<?php

namespace App\Enums;

/**
 * System access role for authenticated users.
 *
 * This enum is intentionally small right now because the first access split in
 * MICS HUB is only between administrators and teachers.
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Teacher = 'teacher';
}

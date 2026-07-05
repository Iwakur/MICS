<?php

/**
 * MICS source: app Enums StaffCompensationMode. See docs/file-reference.md for its full responsibility.
 */

namespace App\Enums;

enum StaffCompensationMode: string
{
    case Fixed = 'fixed';
    case Dynamic = 'dynamic';
}

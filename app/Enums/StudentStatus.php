<?php

/**
 * MICS HUB source: app Enums StudentStatus. See docs/file-reference.md for its full responsibility.
 */

namespace App\Enums;

enum StudentStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Archived = 'archived';
}

<?php

/**
 * MICS HUB source: app Enums ReviewStatus. See docs/file-reference.md for its full responsibility.
 */

namespace App\Enums;

enum ReviewStatus: string
{
    case Draft = 'draft';
    case Validated = 'validated';
}

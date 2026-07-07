<?php

/**
 * MICS HUB source: app Enums StudentBillingType. See docs/file-reference.md for its full responsibility.
 */

namespace App\Enums;

enum StudentBillingType: string
{
    case PerLesson = 'per_lesson';
    case PlanBased = 'plan_based';
}

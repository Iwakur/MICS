<?php

namespace App\Enums;

enum StudentBillingType: string
{
    case PerLesson = 'per_lesson';
    case PlanBased = 'plan_based';
}

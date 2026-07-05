<?php

/**
 * MICS source: app Enums BillingMonthStatus. See docs/file-reference.md for its full responsibility.
 */

namespace App\Enums;

enum BillingMonthStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
}

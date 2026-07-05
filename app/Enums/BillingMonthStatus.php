<?php

namespace App\Enums;

enum BillingMonthStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
}

<?php

namespace App\Support;

use App\Enums\BillingMonthStatus;
use App\Models\BillingMonth;
use Carbon\CarbonImmutable;

final class EffectiveMonth
{
    public static function nextEditable(): CarbonImmutable
    {
        $latestClosed = BillingMonth::query()
            ->where('status', BillingMonthStatus::Closed)
            ->max('month_date');

        $current = CarbonImmutable::now()->startOfMonth();

        if (! $latestClosed) {
            return $current;
        }

        return max($current, CarbonImmutable::parse($latestClosed)->startOfMonth()->addMonth());
    }
}

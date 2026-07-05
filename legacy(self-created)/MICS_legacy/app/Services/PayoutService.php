<?php

declare(strict_types=1);

namespace App\Services;

use DateInterval;
use DateTimeInterface;

final class PayoutService
{
    public const STATUSES = ['draft', 'posted', 'void'];

    public function currentMonthPeriod(): array
    {
        $now = current_app_datetime();
        $monthStart = $now->modify('first day of this month')->setTime(0, 0, 0);
        $nextMonthStart = $monthStart->add(new DateInterval('P1M'));

        return [
            'label' => $monthStart->format('F Y'),
            'month_start' => $monthStart,
            'next_month_start' => $nextMonthStart,
        ];
    }

    public function validateDraftCreation(?array $staffSuggestion, bool $monthRecordExists): array
    {
        if ($staffSuggestion === null) {
            return ['valid' => false, 'message' => 'Unable to find that staff payout suggestion.'];
        }

        if ((float) $staffSuggestion['suggested_amount'] <= 0) {
            return ['valid' => false, 'message' => 'Draft payouts can only be created when the calculated amount is greater than zero.'];
        }

        if ($monthRecordExists) {
            return ['valid' => false, 'message' => 'A payout record already exists for that staff member in the current month.'];
        }

        return ['valid' => true, 'message' => null];
    }

    public function validatePostTransition(?array $payout): array
    {
        if ($payout === null) {
            return ['valid' => false, 'message' => 'Payout record not found.'];
        }

        if (($payout['status'] ?? null) !== 'draft') {
            return ['valid' => false, 'message' => 'Only draft payouts can be posted.'];
        }

        return ['valid' => true, 'message' => null];
    }

    public function validateVoidTransition(?array $payout): array
    {
        if ($payout === null) {
            return ['valid' => false, 'message' => 'Payout record not found.'];
        }

        if (($payout['status'] ?? null) === 'void') {
            return ['valid' => false, 'message' => 'That payout is already void.'];
        }

        return ['valid' => true, 'message' => null];
    }

    public function asSqlTimestamp(DateTimeInterface $dateTime): string
    {
        return $dateTime->format('Y-m-d H:i:sP');
    }
}

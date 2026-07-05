<?php

namespace App\Services;

use App\Enums\ReviewStatus;
use App\Models\BankMonth;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\User;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class BankReconciliationService
{
    /** @return array{opening:string, receipts:string, expenses:string, expected:string} */
    public function totals(CarbonImmutable $month): array
    {
        $previous = BankMonth::query()
            ->where('status', 'reconciled')
            ->whereDate('month_date', '<', $month)
            ->latest('month_date')
            ->first();
        $openingCents = Money::cents($previous ? $previous->closing_balance : 0);
        $receiptsCents = Payment::query()
            ->where('status', ReviewStatus::Validated)
            ->whereBetween('paid_at', [$month->startOfMonth(), $month->endOfMonth()])
            ->pluck('amount')->sum(fn (string $amount): int => Money::cents($amount));
        $expensesCents = Expense::query()
            ->where('status', ReviewStatus::Validated)
            ->whereDate('month_date', $month)
            ->pluck('amount')->sum(fn (string $amount): int => Money::cents($amount));

        return [
            'opening' => Money::decimal($openingCents),
            'receipts' => Money::decimal($receiptsCents),
            'expenses' => Money::decimal($expensesCents),
            'expected' => Money::decimal($openingCents + $receiptsCents - $expensesCents),
        ];
    }

    public function reconcile(CarbonImmutable $month, string $actual, ?string $reason, ?string $note, User $admin): BankMonth
    {
        return DB::transaction(function () use ($month, $actual, $reason, $note, $admin): BankMonth {
            $bankMonth = BankMonth::query()->firstOrCreate(['month_date' => $month]);
            $bankMonth = BankMonth::query()->lockForUpdate()->findOrFail($bankMonth->id);
            if ($bankMonth->status === 'reconciled') {
                throw new UnprocessableEntityHttpException('This bank month is already reconciled.');
            }

            $totals = $this->totals($month);
            $variance = Money::cents($actual) - Money::cents($totals['expected']);
            if ($variance !== 0 && blank($reason)) {
                throw new UnprocessableEntityHttpException('A variance reason is required when actual and expected balances differ.');
            }

            $bankMonth->update([
                'opening_balance' => $totals['opening'],
                'expected_closing_balance' => $totals['expected'],
                'closing_balance' => Money::decimal(Money::cents($actual)),
                'status' => 'reconciled',
                'variance_reason' => $reason,
                'reconciled_by_user_id' => $admin->id,
                'reconciled_at' => now(),
                'note' => $note,
            ]);
            $bankMonth->events()->create(['user_id' => $admin->id, 'action' => 'reconciled', 'reason' => $reason, 'occurred_at' => now()]);

            return $bankMonth->refresh();
        }, 3);
    }

    public function reopen(BankMonth $bankMonth, string $reason, User $admin): BankMonth
    {
        return DB::transaction(function () use ($bankMonth, $reason, $admin): BankMonth {
            $locked = BankMonth::query()->lockForUpdate()->findOrFail($bankMonth->id);
            if ($locked->status !== 'reconciled') {
                throw new UnprocessableEntityHttpException('Only a reconciled bank month can be reopened.');
            }
            $locked->update(['status' => 'draft', 'reconciled_by_user_id' => null, 'reconciled_at' => null]);
            $locked->events()->create(['user_id' => $admin->id, 'action' => 'reopened', 'reason' => $reason, 'occurred_at' => now()]);

            return $locked->refresh();
        }, 3);
    }
}

<?php

namespace App\Services;

use App\Enums\ReviewStatus;
use App\Models\Expense;
use App\Models\StudentMonth;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Builds read-only monthly finance figures from persisted snapshots.
 *
 * Positive debt and student credit remain separate so credits cannot conceal
 * receivables belonging to other students.
 */
class FinanceSummaryService
{
    /** @return array<string, mixed> */
    public function forMonth(CarbonImmutable $month): array
    {
        $studentMonths = StudentMonth::query()
            ->with('student.teacher')
            ->withSum('validatedPayments', 'amount')
            ->whereDate('month_date', $month)
            ->orderBy('student_id')
            ->get();

        $studentRows = $studentMonths->map(function (StudentMonth $studentMonth): array {
            $paymentsCents = Money::cents($studentMonth->validated_payments_sum_amount);
            $chargesCents = Money::cents($studentMonth->charge_amount) + Money::cents($studentMonth->manual_adjustment);
            $balanceCents = Money::cents($studentMonth->opening_balance) + $chargesCents - $paymentsCents;

            return [
                'student_month' => $studentMonth,
                'charges' => Money::display($chargesCents),
                'charges_cents' => $chargesCents,
                'payments' => Money::display($paymentsCents),
                'payments_cents' => $paymentsCents,
                'balance' => Money::display($balanceCents),
                'balance_cents' => $balanceCents,
            ];
        });

        $expenses = Expense::query()->whereDate('month_date', $month)->get();

        return [
            'student_rows' => $studentRows,
            'opening_balance' => Money::display($this->sumCents($studentMonths, 'opening_balance')),
            'charges' => Money::display((int) $studentRows->sum('charges_cents')),
            'validated_payments' => Money::display((int) $studentRows->sum('payments_cents')),
            'outstanding_debt' => Money::display((int) $studentRows->sum(fn (array $row): int => max(0, $row['balance_cents']))),
            'student_credit' => Money::display(abs((int) $studentRows->sum(fn (array $row): int => min(0, $row['balance_cents'])))),
            'students_with_debt' => $studentRows->where('balance_cents', '>', 0)->count(),
            'validated_salaries' => $this->expenseTotal($expenses, true, ReviewStatus::Validated),
            'draft_salaries' => $this->expenseTotal($expenses, true, ReviewStatus::Draft),
            'validated_manual_expenses' => $this->expenseTotal($expenses, false, ReviewStatus::Validated),
            'draft_manual_expenses' => $this->expenseTotal($expenses, false, ReviewStatus::Draft),
        ];
    }

    private function expenseTotal(Collection $expenses, bool $generated, ReviewStatus $status): float
    {
        $cents = $expenses
            ->where('is_auto_generated', $generated)
            ->where('status', $status)
            ->sum(fn (Expense $expense): int => Money::cents($expense->amount));

        return Money::display((int) $cents);
    }

    private function sumCents(Collection $models, string $field): int
    {
        return (int) $models->sum(fn ($model): int => Money::cents($model->{$field}));
    }
}

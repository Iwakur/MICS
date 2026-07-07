<?php

namespace App\Services;

use App\Enums\BillingMonthStatus;
use App\Enums\ReviewStatus;
use App\Enums\StaffCompensationMode;
use App\Enums\StudentBillingType;
use App\Enums\StudentStatus;
use App\Models\BillingMonth;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\LessonType;
use App\Models\LessonTypeRate;
use App\Models\Plan;
use App\Models\PlanRate;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentConfiguration;
use App\Models\StudentMonth;
use App\Models\User;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Converts mutable monthly activity into reviewable financial snapshots.
 *
 * Draft generation is deliberately manual, chronological, and transactional.
 * Regeneration after an audited unlock refreshes drafts but never overwrites
 * validated records.
 */
class MonthClosingService
{
    /** @return array{students: Collection, salaries: Collection, student_total: float, salary_total: float} */
    public function preview(CarbonImmutable $month): array
    {
        $students = $this->studentCalculations($month);
        $salaries = $this->salaryCalculations($students);

        return [
            'students' => $students,
            'salaries' => $salaries,
            'student_total' => Money::display($students->sum('charge_cents')),
            'salary_total' => Money::display($salaries->sum('amount_cents')),
        ];
    }

    /** @return Collection<int, CarbonImmutable> */
    public function pendingMonthsThrough(CarbonImmutable $month): Collection
    {
        $selected = BillingMonth::query()->whereDate('month_date', $month)->first();
        if ($selected?->status === BillingMonthStatus::Closed) {
            return collect();
        }

        return BillingMonth::query()
            ->whereDate('month_date', '<', $month)
            ->where('status', BillingMonthStatus::Open)
            ->orderBy('month_date')
            ->pluck('month_date')
            ->map(fn (string $date): CarbonImmutable => CarbonImmutable::parse($date)->startOfMonth())
            ->push($month->startOfMonth());
    }

    public function close(CarbonImmutable $month, User $admin): BillingMonth
    {
        return DB::transaction(function () use ($month, $admin): BillingMonth {
            $selectedMonth = BillingMonth::query()->firstOrCreate(['month_date' => $month]);
            $selectedMonth = BillingMonth::query()->lockForUpdate()->findOrFail($selectedMonth->id);

            if ($selectedMonth->status === BillingMonthStatus::Closed) {
                throw new UnprocessableEntityHttpException('This billing month is already closed.');
            }

            $earliestOpenMonth = BillingMonth::query()
                ->whereDate('month_date', '<=', $month)
                ->where('status', BillingMonthStatus::Open)
                ->min('month_date');

            if ($earliestOpenMonth && BillingMonth::query()->whereDate('month_date', '>', $earliestOpenMonth)->where('status', BillingMonthStatus::Closed)->exists()) {
                throw ValidationException::withMessages([
                    'month' => __('messages.later_month_already_locked'),
                ]);
            }

            $monthsToClose = BillingMonth::query()
                ->whereDate('month_date', '<=', $month)
                ->where('status', BillingMonthStatus::Open)
                ->orderBy('month_date')
                ->lockForUpdate()
                ->get();

            foreach ($monthsToClose as $billingMonth) {
                $this->closeMonth($billingMonth, $admin);
            }

            return $selectedMonth->refresh();
        }, 3);
    }

    private function closeMonth(BillingMonth $billingMonth, User $admin): void
    {
        $month = CarbonImmutable::parse($billingMonth->month_date)->startOfMonth();
        $preview = $this->preview($month);
        $students = Student::query()
            ->whereDate('joined_at', '<=', $month->endOfMonth())
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
        $this->ensureStudentMonths($month, $students);
        StudentMonth::query()
            ->whereDate('month_date', $month)
            ->where('status', ReviewStatus::Draft)
            ->update(['charge_amount' => 0]);
        $this->persistStudentMonths($month, $preview['students']);
        $this->persistOpeningBalances($month);
        $this->persistSalaryDrafts($month, $preview['salaries']);

        $billingMonth->update([
            'status' => BillingMonthStatus::Closed,
            'closed_by_user_id' => $admin->id,
            'closed_at' => now(),
        ]);
        $billingMonth->events()->create([
            'user_id' => $admin->id,
            'action' => 'closed',
            'occurred_at' => now(),
        ]);
    }

    private function ensureStudentMonths(CarbonImmutable $month, Collection $students): void
    {
        foreach ($students as $student) {
            $previous = StudentMonth::query()
                ->with('validatedPayments')
                ->whereBelongsTo($student)
                ->whereDate('month_date', '<', $month)
                ->latest('month_date')
                ->first();

            $studentMonth = StudentMonth::query()->firstOrCreate([
                'student_id' => $student->id,
                'month_date' => $month,
            ]);

            if ($previous) {
                $studentMonth->update(['opening_balance' => $previous->closingBalanceAmount()]);
            }
        }
    }

    public function reopen(CarbonImmutable $month, User $admin, string $reason): BillingMonth
    {
        return DB::transaction(function () use ($month, $admin, $reason): BillingMonth {
            $billingMonth = BillingMonth::query()->whereDate('month_date', $month)->lockForUpdate()->first();

            if (! $billingMonth || $billingMonth->status !== BillingMonthStatus::Closed) {
                throw new UnprocessableEntityHttpException('Only a closed billing month can be reopened.');
            }

            if (BillingMonth::query()->whereDate('month_date', '>', $month)->where('status', BillingMonthStatus::Closed)->exists()) {
                throw ValidationException::withMessages([
                    'month' => __('messages.unlock_latest_month_first'),
                ]);
            }

            $billingMonth->update(['status' => BillingMonthStatus::Open]);
            $billingMonth->events()->create([
                'user_id' => $admin->id,
                'action' => 'reopened',
                'reason' => $reason,
                'occurred_at' => now(),
            ]);

            return $billingMonth->refresh();
        }, 3);
    }

    private function studentCalculations(CarbonImmutable $month): Collection
    {
        $students = Student::query()
            ->with([
                'configurations' => fn ($query) => $query
                    ->whereDate('effective_from', '<=', $month)
                    ->with(['teacher', 'lessonType.rates', 'plan.rates'])
                    ->latest('effective_from'),
            ])
            ->with(['months' => fn ($query) => $query->whereDate('month_date', $month)])
            ->whereDate('joined_at', '<=', $month->endOfMonth())
            ->orderBy('first_name')
            ->get();
        $calculations = collect();

        foreach ($students as $student) {
            $configuration = $student->configurations->first();
            if (! $configuration instanceof StudentConfiguration || $configuration->status !== StudentStatus::Active) {
                continue;
            }

            $isPerLesson = $configuration->getAttribute('billing_type') === StudentBillingType::PerLesson;
            if (! $isPerLesson && (! $configuration->plan_start_at || CarbonImmutable::parse($configuration->plan_start_at)->gt($month->endOfMonth()))) {
                continue;
            }

            $studentMonth = $student->months->first();
            $units = $isPerLesson ? ($studentMonth?->lesson_count ?? 0) : 1;
            if ($isPerLesson) {
                $catalog = $configuration->lessonType;
                abort_unless($catalog instanceof LessonType, 422, "Missing lesson type for {$student->first_name}.");
                $rate = $catalog->rates->where('effective_from', '<=', $month)->sortByDesc('effective_from')->first();
                abort_unless($rate instanceof LessonTypeRate, 422, "No effective lesson rate exists for {$student->first_name} in {$month->format('Y-m')}.");
                $schoolRateCents = Money::cents($rate->lesson_price);
                $teacherRateCents = Money::cents($rate->teacher_share_per_lesson);
            } else {
                $catalog = $configuration->plan;
                abort_unless($catalog instanceof Plan, 422, "Missing plan for {$student->first_name}.");
                $rate = $catalog->rates->where('effective_from', '<=', $month)->sortByDesc('effective_from')->first();
                abort_unless($rate instanceof PlanRate, 422, "No effective plan rate exists for {$student->first_name} in {$month->format('Y-m')}.");
                $schoolRateCents = Money::cents($rate->plan_price);
                $teacherRateCents = Money::cents($rate->teacher_monthly_amount);
            }
            $grossChargeCents = $units * $schoolRateCents;
            $discountCents = Money::cents($configuration->discount_amount);
            $chargeCents = max(0, $grossChargeCents - $discountCents);
            $teacherAmountCents = $units * $teacherRateCents;

            $calculations->push([
                'student' => $student,
                'student_month' => $studentMonth,
                'source_type' => $isPerLesson ? 'per_lesson' : 'plan_based',
                'description' => $catalog->name,
                'teacher_id' => $configuration->staff_id,
                'units' => $units,
                'school_rate' => Money::display($schoolRateCents),
                'teacher_rate' => Money::display($teacherRateCents),
                'teacher_rate_value' => Money::decimal($teacherRateCents),
                'gross_charge' => Money::display($grossChargeCents),
                'discount' => Money::display($discountCents),
                'charge' => Money::display($chargeCents),
                'charge_cents' => $chargeCents,
                'charge_value' => Money::decimal($chargeCents),
                'teacher_amount' => Money::display($teacherAmountCents),
                'teacher_amount_cents' => $teacherAmountCents,
                'teacher_amount_value' => Money::decimal($teacherAmountCents),
            ]);
        }

        return $calculations;
    }

    private function salaryCalculations(Collection $students): Collection
    {
        return Staff::query()->where('is_active', true)->orderBy('first_name')->get()
            ->map(function (Staff $staff) use ($students): array {
                if ($staff->compensation_mode === StaffCompensationMode::Fixed) {
                    $amountCents = Money::cents($staff->salary_amount);
                    $sources = collect([[
                        'student' => null,
                        'source_type' => 'fixed',
                        'description' => 'Fixed monthly salary',
                        'units' => 1,
                        'rate' => Money::display($amountCents),
                        'rate_value' => Money::decimal($amountCents),
                        'amount' => Money::display($amountCents),
                        'amount_value' => Money::decimal($amountCents),
                    ]]);
                } else {
                    $sources = $students->filter(fn (array $item) => $item['teacher_id'] === $staff->id)
                        ->map(fn (array $item) => [
                            'student' => $item['student'],
                            'source_type' => $item['source_type'],
                            'description' => $item['description'],
                            'units' => $item['units'],
                            'rate' => $item['teacher_rate'],
                            'rate_value' => $item['teacher_rate_value'],
                            'amount' => $item['teacher_amount'],
                            'amount_value' => $item['teacher_amount_value'],
                        ])->values();
                    $amountCents = (int) $students
                        ->filter(fn (array $item) => $item['teacher_id'] === $staff->id)
                        ->sum('teacher_amount_cents');
                }

                return [
                    'staff' => $staff,
                    'amount' => Money::display($amountCents),
                    'amount_cents' => $amountCents,
                    'amount_value' => Money::decimal($amountCents),
                    'sources' => $sources,
                ];
            })
            ->filter(fn (array $salary): bool => $salary['amount_cents'] > 0)
            ->values();
    }

    private function persistStudentMonths(CarbonImmutable $month, Collection $students): void
    {
        foreach ($students as $item) {
            $studentMonth = StudentMonth::query()->firstOrCreate([
                'student_id' => $item['student']->id,
                'month_date' => $month,
            ]);

            if ($studentMonth->status === ReviewStatus::Draft) {
                $studentMonth->update(['charge_amount' => $item['charge_value']]);
            }
        }
    }

    private function persistOpeningBalances(CarbonImmutable $month): void
    {
        StudentMonth::query()
            ->with('validatedPayments')
            ->whereDate('month_date', $month)
            ->orderBy('student_id')
            ->lockForUpdate()
            ->get()
            ->each(function (StudentMonth $studentMonth) use ($month): void {
                StudentMonth::query()->updateOrCreate(
                    ['student_id' => $studentMonth->student_id, 'month_date' => $month->addMonth()],
                    ['opening_balance' => $studentMonth->closingBalanceAmount()],
                );
            });
    }

    private function persistSalaryDrafts(CarbonImmutable $month, Collection $salaries): void
    {
        $salaryCategory = ExpenseCategory::query()->firstOrCreate(
            ['name' => 'Зарплата'],
            ['note' => 'Generated staff salary drafts.'],
        );

        $generationKeys = $salaries
            ->map(fn (array $salary): string => "salary:{$month->format('Y-m')}:staff:{$salary['staff']->id}");
        $obsoleteDrafts = Expense::query()
            ->whereDate('month_date', $month)
            ->where('is_auto_generated', true)
            ->where('status', ReviewStatus::Draft);
        if ($generationKeys->isNotEmpty()) {
            $obsoleteDrafts->whereNotIn('generation_key', $generationKeys);
        }
        $obsoleteDrafts->delete();

        foreach ($salaries as $salary) {
            $expense = Expense::query()->firstOrCreate(
                ['generation_key' => "salary:{$month->format('Y-m')}:staff:{$salary['staff']->id}"],
                [
                    'staff_id' => $salary['staff']->id,
                    'expense_category_id' => $salaryCategory->id,
                    'month_date' => $month,
                    'amount' => $salary['amount_value'],
                    'status' => ReviewStatus::Draft,
                    'is_auto_generated' => true,
                    'note' => 'Generated by month closing.',
                ],
            );

            if ($expense->status === ReviewStatus::Validated) {
                continue;
            }

            $expense->update([
                'staff_id' => $salary['staff']->id,
                'expense_category_id' => $salaryCategory->id,
                'month_date' => $month,
                'amount' => $salary['amount_value'],
                'status' => ReviewStatus::Draft,
                'is_auto_generated' => true,
                'note' => 'Generated by month closing.',
            ]);

            $expense->salarySources()->delete();
            $expense->salarySources()->createMany($salary['sources']->map(fn (array $source) => [
                'student_id' => $source['student']?->id,
                'source_type' => $source['source_type'],
                'description' => $source['description'],
                'units' => $source['units'],
                'rate' => $source['rate_value'],
                'amount' => $source['amount_value'],
            ])->all());
        }
    }
}

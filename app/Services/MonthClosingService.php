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
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentMonth;
use App\Models\User;
use App\Support\Money;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Converts mutable monthly activity into reviewable financial snapshots.
 *
 * Closing is deliberately manual and transactional. Reclosing after an
 * audited reopen refreshes drafts but never overwrites validated records.
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

    public function close(CarbonImmutable $month, User $admin): BillingMonth
    {
        return DB::transaction(function () use ($month, $admin): BillingMonth {
            $billingMonth = BillingMonth::query()->firstOrCreate(['month_date' => $month]);
            $billingMonth = BillingMonth::query()->lockForUpdate()->findOrFail($billingMonth->id);

            if ($billingMonth->status === BillingMonthStatus::Closed) {
                throw new UnprocessableEntityHttpException('This billing month is already closed.');
            }

            $preview = $this->preview($month);
            Student::query()
                ->whereKey($preview['students']->pluck('student.id')->sort()->values())
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
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

            return $billingMonth->refresh();
        }, 3);
    }

    public function reopen(CarbonImmutable $month, User $admin, string $reason): BillingMonth
    {
        return DB::transaction(function () use ($month, $admin, $reason): BillingMonth {
            $billingMonth = BillingMonth::query()->whereDate('month_date', $month)->lockForUpdate()->first();

            if (! $billingMonth || $billingMonth->status !== BillingMonthStatus::Closed) {
                throw new UnprocessableEntityHttpException('Only a closed billing month can be reopened.');
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
        return Student::query()
            ->with(['teacher', 'lessonType', 'plan'])
            ->with(['months' => fn ($query) => $query->whereDate('month_date', $month)])
            ->where('status', StudentStatus::Active)
            ->whereDate('joined_at', '<=', $month->endOfMonth())
            ->where(function ($query) use ($month): void {
                $query->where('billing_type', StudentBillingType::PerLesson)
                    ->orWhere(function ($query) use ($month): void {
                        $query->where('billing_type', StudentBillingType::PlanBased)
                            ->whereDate('plan_start_at', '<=', $month->endOfMonth());
                    });
            })
            ->orderBy('first_name')
            ->get()
            ->map(function (Student $student): array {
                $studentMonth = $student->months->first();
                $isPerLesson = $student->billing_type === StudentBillingType::PerLesson;
                $units = $isPerLesson ? ($studentMonth?->lesson_count ?? 0) : 1;
                $schoolRateCents = Money::cents($isPerLesson ? $student->lessonType->lesson_price : $student->plan->plan_price);
                $teacherRateCents = Money::cents($isPerLesson ? $student->lessonType->teacher_share_per_lesson : $student->plan->teacher_monthly_amount);
                $grossChargeCents = $units * $schoolRateCents;
                $discountCents = Money::cents($student->discount_amount);
                $chargeCents = max(0, $grossChargeCents - $discountCents);
                $teacherAmountCents = $units * $teacherRateCents;

                return [
                    'student' => $student,
                    'student_month' => $studentMonth,
                    'source_type' => $isPerLesson ? 'per_lesson' : 'plan_based',
                    'description' => $isPerLesson ? $student->lessonType->name : $student->plan->name,
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
                ];
            });
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
                    $sources = $students->filter(fn (array $item) => $item['student']->staff_id === $staff->id)
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
                        ->filter(fn (array $item) => $item['student']->staff_id === $staff->id)
                        ->sum('teacher_amount_cents');
                }

                return [
                    'staff' => $staff,
                    'amount' => Money::display($amountCents),
                    'amount_cents' => $amountCents,
                    'amount_value' => Money::decimal($amountCents),
                    'sources' => $sources,
                ];
            });
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
            ['name' => 'Salary'],
            ['note' => 'Generated staff salary drafts.'],
        );

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

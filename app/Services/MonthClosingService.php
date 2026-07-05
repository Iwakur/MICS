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
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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
            'student_total' => round($students->sum('charge'), 2),
            'salary_total' => round($salaries->sum('amount'), 2),
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
            $this->persistStudentMonths($month, $preview['students']);
            $this->persistSalaryDrafts($month, $preview['salaries']);

            $billingMonth->update([
                'status' => BillingMonthStatus::Closed,
                'closed_by_user_id' => $admin->id,
                'closed_at' => now(),
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
                $schoolRate = (float) ($isPerLesson ? $student->lessonType->lesson_price : $student->plan->plan_price);
                $teacherRate = (float) ($isPerLesson ? $student->lessonType->teacher_share_per_lesson : $student->plan->teacher_monthly_amount);
                $grossCharge = round($units * $schoolRate, 2);

                return [
                    'student' => $student,
                    'student_month' => $studentMonth,
                    'source_type' => $isPerLesson ? 'per_lesson' : 'plan_based',
                    'description' => $isPerLesson ? $student->lessonType->name : $student->plan->name,
                    'units' => $units,
                    'school_rate' => $schoolRate,
                    'teacher_rate' => $teacherRate,
                    'gross_charge' => $grossCharge,
                    'discount' => (float) $student->discount_amount,
                    'charge' => max(0, round($grossCharge - (float) $student->discount_amount, 2)),
                    'teacher_amount' => round($units * $teacherRate, 2),
                ];
            });
    }

    private function salaryCalculations(Collection $students): Collection
    {
        return Staff::query()->where('is_active', true)->orderBy('first_name')->get()
            ->map(function (Staff $staff) use ($students): array {
                if ($staff->compensation_mode === StaffCompensationMode::Fixed) {
                    $amount = (float) ($staff->salary_amount ?? 0);
                    $sources = collect([[
                        'student' => null,
                        'source_type' => 'fixed',
                        'description' => 'Fixed monthly salary',
                        'units' => 1,
                        'rate' => $amount,
                        'amount' => $amount,
                    ]]);
                } else {
                    $sources = $students->filter(fn (array $item) => $item['student']->staff_id === $staff->id)
                        ->map(fn (array $item) => [
                            'student' => $item['student'],
                            'source_type' => $item['source_type'],
                            'description' => $item['description'],
                            'units' => $item['units'],
                            'rate' => $item['teacher_rate'],
                            'amount' => $item['teacher_amount'],
                        ])->values();
                    $amount = round($sources->sum('amount'), 2);
                }

                return ['staff' => $staff, 'amount' => $amount, 'sources' => $sources];
            });
    }

    private function persistStudentMonths(CarbonImmutable $month, Collection $students): void
    {
        foreach ($students as $item) {
            $studentMonth = StudentMonth::query()->updateOrCreate(
                ['student_id' => $item['student']->id, 'month_date' => $month],
                ['charge_amount' => $item['charge']],
            );

            $validatedPayments = $studentMonth->validatedPayments()->sum('amount');
            $closingBalance = round(
                (float) $studentMonth->opening_balance + (float) $studentMonth->charge_amount
                + (float) $studentMonth->manual_adjustment - (float) $validatedPayments,
                2,
            );

            StudentMonth::query()->updateOrCreate(
                ['student_id' => $item['student']->id, 'month_date' => $month->addMonth()],
                ['opening_balance' => $closingBalance],
            );
        }
    }

    private function persistSalaryDrafts(CarbonImmutable $month, Collection $salaries): void
    {
        $salaryCategory = ExpenseCategory::query()->firstOrCreate(
            ['name' => 'Salary'],
            ['note' => 'Generated staff salary drafts.'],
        );

        foreach ($salaries as $salary) {
            $expense = Expense::query()->updateOrCreate(
                ['generation_key' => "salary:{$month->format('Y-m')}:staff:{$salary['staff']->id}"],
                [
                    'staff_id' => $salary['staff']->id,
                    'expense_category_id' => $salaryCategory->id,
                    'month_date' => $month,
                    'amount' => $salary['amount'],
                    'status' => ReviewStatus::Draft,
                    'is_auto_generated' => true,
                    'note' => 'Generated by month closing.',
                ],
            );

            $expense->salarySources()->delete();
            $expense->salarySources()->createMany($salary['sources']->map(fn (array $source) => [
                'student_id' => $source['student']?->id,
                'source_type' => $source['source_type'],
                'description' => $source['description'],
                'units' => $source['units'],
                'rate' => $source['rate'],
                'amount' => $source['amount'],
            ])->all());
        }
    }
}

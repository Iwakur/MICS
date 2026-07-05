<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentMonth;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Maintains the derived balance chain without storing debt on students.
 *
 * Any validated correction can change every later opening balance, so callers
 * propagate from the changed month instead of updating only one successor.
 */
class StudentBalanceService
{
    public function findOrCreateMonth(int $studentId, CarbonImmutable $month): StudentMonth
    {
        return DB::transaction(function () use ($studentId, $month): StudentMonth {
            $this->lockStudent($studentId);

            $existing = StudentMonth::query()
                ->where('student_id', $studentId)
                ->whereDate('month_date', $month)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $previous = StudentMonth::query()
                ->where('student_id', $studentId)
                ->whereDate('month_date', '<', $month)
                ->latest('month_date')
                ->lockForUpdate()
                ->first();

            $created = StudentMonth::query()->create([
                'student_id' => $studentId,
                'month_date' => $month,
                'opening_balance' => $previous?->closingBalanceAmount() ?? '0.00',
            ]);

            $this->propagateLocked($created);

            return $created;
        }, 3);
    }

    public function propagateFrom(StudentMonth $source, bool $createNextMonth = false): void
    {
        DB::transaction(function () use ($source, $createNextMonth): void {
            $this->lockStudent($source->student_id);
            $lockedSource = StudentMonth::query()->lockForUpdate()->findOrFail($source->id);

            $this->propagateLocked($lockedSource, $createNextMonth);
        }, 3);
    }

    private function propagateLocked(StudentMonth $source, bool $createNextMonth = false): void
    {
        if ($createNextMonth) {
            StudentMonth::query()->firstOrCreate([
                'student_id' => $source->student_id,
                'month_date' => CarbonImmutable::parse($source->month_date)->addMonth(),
            ]);
        }

        $previous = $source->fresh();
        $futureMonths = StudentMonth::query()
            ->where('student_id', $source->student_id)
            ->whereDate('month_date', '>', $source->month_date)
            ->orderBy('month_date')
            ->lockForUpdate()
            ->get();

        foreach ($futureMonths as $month) {
            $month->update(['opening_balance' => $previous->closingBalanceAmount()]);
            $previous = $month->fresh();
        }
    }

    private function lockStudent(int $studentId): void
    {
        Student::query()->whereKey($studentId)->lockForUpdate()->firstOrFail();
    }
}

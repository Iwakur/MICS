<?php

namespace App\Http\Requests;

use App\Enums\BillingMonthStatus;
use App\Enums\StudentBillingType;
use App\Enums\StudentStatus;
use App\Models\BillingMonth;
use App\Models\Student;
use App\Models\StudentConfiguration;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveMonthlyLessonCountsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'date_format:Y-m'],
            'counts' => ['required', 'array'],
            'counts.*' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function monthDate(): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m', $this->string('month')->toString());
    }

    /** @return array<int, int> */
    public function lessonCounts(): array
    {
        return collect($this->validated('counts'))
            ->mapWithKeys(fn (mixed $count, string|int $studentId) => [(int) $studentId => (int) $count])
            ->all();
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (BillingMonth::query()->whereDate('month_date', $this->monthDate())->where('status', BillingMonthStatus::Closed)->exists()) {
                $validator->errors()->add('month', __('messages.counts_after_close'));

                return;
            }

            $studentIds = array_map('intval', array_keys($this->input('counts', [])));
            $eligibleCount = Student::query()
                ->with(['configurations' => fn ($query) => $query->whereDate('effective_from', '<=', $this->monthDate())->latest('effective_from')])
                ->whereKey($studentIds)
                ->whereDate('joined_at', '<=', $this->monthDate()->endOfMonth())
                ->get()
                ->filter(function (Student $student): bool {
                    $configuration = $student->configurations->first();

                    return $configuration instanceof StudentConfiguration
                        && $configuration->status === StudentStatus::Active
                        && $configuration->billing_type === StudentBillingType::PerLesson
                        && ($this->user()->isAdmin() || $configuration->staff_id === $this->user()->staff_id);
                })->count();

            if ($eligibleCount !== count($studentIds)) {
                $validator->errors()->add('counts', __('messages.students_unavailable'));
            }
        }];
    }
}

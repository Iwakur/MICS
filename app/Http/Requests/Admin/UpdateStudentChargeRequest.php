<?php

/**
 * MICS HUB source: app Http Requests Admin UpdateStudentChargeRequest. See docs/file-reference.md for its full responsibility.
 */

namespace App\Http\Requests\Admin;

use App\Enums\BillingMonthStatus;
use App\Enums\ReviewStatus;
use App\Models\BillingMonth;
use App\Models\StudentMonth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class UpdateStudentChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $studentMonth = $this->route('studentMonth');

        return $this->user()?->isAdmin() === true
            && $studentMonth instanceof StudentMonth
            && $studentMonth->status === ReviewStatus::Draft;
    }

    public function rules(): array
    {
        return [
            'manual_adjustment' => ['required', 'numeric'],
            'adjustment_reason' => [
                Rule::requiredIf(fn (): bool => (float) $this->input('manual_adjustment', 0) !== 0.0),
                'nullable',
                'string',
                'max:2000',
            ],
            'status' => ['required', new Enum(ReviewStatus::class)],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $studentMonth = $this->route('studentMonth');

            if ($studentMonth instanceof StudentMonth && ! BillingMonth::query()
                ->whereDate('month_date', $studentMonth->month_date)
                ->where('status', BillingMonthStatus::Closed)
                ->exists()) {
                $validator->errors()->add('status', __('messages.generate_before_charge_review'));
            }
        }];
    }
}

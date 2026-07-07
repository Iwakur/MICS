<?php

/**
 * MICS HUB source: app Http Requests SaveStudentRequest. See docs/file-reference.md for its full responsibility.
 */

namespace App\Http\Requests;

use App\Enums\StudentBillingType;
use App\Enums\StudentStatus;
use App\Models\Staff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class SaveStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isAdmin = $this->user()?->isAdmin() === true;
        $statuses = $isAdmin
            ? array_column(StudentStatus::cases(), 'value')
            : [StudentStatus::Active->value, StudentStatus::Paused->value];

        return [
            'staff_id' => [Rule::requiredIf($isAdmin), 'nullable', 'exists:staff,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'family_name' => ['nullable', 'string', 'max:100'],
            'father_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'city' => ['nullable', 'string', 'max:100'],
            'joined_at' => ['required', 'date'],
            'status' => ['required', Rule::in($statuses)],
            'billing_type' => ['required', new Enum(StudentBillingType::class)],
            'lesson_type_id' => [
                Rule::requiredIf($this->input('billing_type') === StudentBillingType::PerLesson->value),
                'nullable',
                'exists:lesson_types,id',
            ],
            'plan_id' => [
                Rule::requiredIf($this->input('billing_type') === StudentBillingType::PlanBased->value),
                'nullable',
                'exists:plans,id',
            ],
            'plan_start_at' => [
                Rule::requiredIf($this->input('billing_type') === StudentBillingType::PlanBased->value),
                'nullable',
                'date',
            ],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function studentData(?int $forcedStaffId = null): array
    {
        $data = $this->safe()->except(['staff_id']);
        $data['staff_id'] = $forcedStaffId ?? $this->integer('staff_id');
        $data['lesson_amount'] = null;

        if ($this->input('billing_type') === StudentBillingType::PerLesson->value) {
            $data['plan_id'] = null;
            $data['plan_start_at'] = null;
        } else {
            $data['lesson_type_id'] = null;
        }

        return $data;
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->user()?->isAdmin() || ! $this->filled('staff_id')) {
                    return;
                }

                $canReceiveStudents = Staff::query()
                    ->whereKey($this->integer('staff_id'))
                    ->where('is_active', true)
                    ->whereHas('role', fn ($query) => $query->where('can_teach', true))
                    ->exists();

                if (! $canReceiveStudents) {
                    $validator->errors()->add('staff_id', 'Choose an active staff member with a teaching role.');
                }
            },
        ];
    }
}

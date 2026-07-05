<?php

namespace App\Http\Requests\Admin;

use App\Models\Plan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_assignable' => $this->boolean('is_assignable')]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique((new Plan)->getTable(), 'name')->ignore($this->route('plan')),
            ],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'lesson_count' => ['required', 'integer', 'min:1'],
            'lesson_price' => ['required', 'numeric', 'min:0'],
            'plan_price' => ['required', 'numeric', 'min:0'],
            'teacher_monthly_amount' => ['required', 'numeric', 'min:0'],
            'is_assignable' => ['required', 'boolean'],
            'note' => ['nullable', 'string'],
        ];
    }
}

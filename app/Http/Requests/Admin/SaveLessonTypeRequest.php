<?php

/**
 * MICS source: app Http Requests Admin SaveLessonTypeRequest. See docs/file-reference.md for its full responsibility.
 */

namespace App\Http\Requests\Admin;

use App\Models\LessonType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveLessonTypeRequest extends FormRequest
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
                Rule::unique((new LessonType)->getTable(), 'name')->ignore($this->route('lesson_type')),
            ],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'lesson_price' => ['required', 'numeric', 'min:0'],
            'teacher_share_per_lesson' => ['required', 'numeric', 'min:0'],
            'is_assignable' => ['required', 'boolean'],
            'note' => ['nullable', 'string'],
        ];
    }
}

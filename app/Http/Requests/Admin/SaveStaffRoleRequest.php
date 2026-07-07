<?php

/**
 * MICS HUB source: app Http Requests Admin SaveStaffRoleRequest. See docs/file-reference.md for its full responsibility.
 */

namespace App\Http\Requests\Admin;

use App\Models\StaffRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveStaffRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'can_teach' => $this->boolean('can_teach'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique((new StaffRole)->getTable(), 'name')->ignore($this->route('staff_role')),
            ],
            'can_teach' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'note' => ['nullable', 'string'],
        ];
    }
}

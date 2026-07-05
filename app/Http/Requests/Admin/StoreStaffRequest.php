<?php

namespace App\Http\Requests\Admin;

use App\Enums\StaffCompensationMode;
use App\Models\Staff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'staff_role_id' => ['required', 'exists:staff_roles,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'family_name' => ['nullable', 'string', 'max:100'],
            'father_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique((new Staff)->getTable(), 'email')],
            'phone' => ['nullable', 'string', 'max:32'],
            'birthday' => ['nullable', 'date'],
            'city' => ['nullable', 'string', 'max:100'],
            'payout_card_number' => ['nullable', 'string', 'max:64'],
            'compensation_mode' => ['required', new Enum(StaffCompensationMode::class)],
            'salary_amount' => [
                Rule::requiredIf($this->input('compensation_mode') === StaffCompensationMode::Fixed->value),
                'nullable',
                'numeric',
                'min:0',
            ],
            'is_active' => ['required', 'boolean'],
            'note' => ['nullable', 'string'],
            'user_id' => [
                'nullable',
                Rule::exists('users', 'id')->whereNull('staff_id'),
            ],
        ];
    }

    public function validatedStaff(): array
    {
        return [
            'staff_role_id' => $this->integer('staff_role_id'),
            'first_name' => $this->string('first_name')->toString(),
            'family_name' => $this->input('family_name'),
            'father_name' => $this->input('father_name'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'birthday' => $this->input('birthday'),
            'city' => $this->input('city'),
            'payout_card_number' => $this->input('payout_card_number'),
            'compensation_mode' => $this->input('compensation_mode'),
            'salary_amount' => $this->input('compensation_mode') === StaffCompensationMode::Fixed->value
                ? $this->input('salary_amount')
                : null,
            'is_active' => $this->boolean('is_active'),
            'note' => $this->input('note'),
        ];
    }

    public function linkedUserId(): ?int
    {
        return $this->filled('user_id') ? $this->integer('user_id') : null;
    }
}

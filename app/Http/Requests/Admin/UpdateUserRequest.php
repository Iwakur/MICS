<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\Staff;
use App\Models\StaffRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

/**
 * Validation for editing a user from the admin panel.
 *
 * The update flow differs from create because unique checks must ignore the
 * current record and the password may be left blank to keep the old value.
 */
class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize checkbox input before the rule set runs.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Keep edits aligned with the users table while allowing unchanged values.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'staff_id' => ['nullable', Rule::exists('staff', 'id')->where('is_active', true), Rule::unique('users', 'staff_id')->ignore($user)],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')->ignore($user)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => ['required', new Enum(UserRole::class)],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->boolean('is_active')) {
                return;
            }
            $staff = Staff::query()->with('role')->find($this->input('staff_id'));
            if (! $staff) {
                $validator->errors()->add('staff_id', 'Every active account must be linked to active staff.');
            } elseif (! $staff->role instanceof StaffRole || ! $staff->role->is_active) {
                $validator->errors()->add('staff_id', 'An active account requires staff with an active role.');
            } elseif ($this->input('role') === UserRole::Teacher->value
                && ! $staff->role->can_teach) {
                $validator->errors()->add('staff_id', 'A teacher account requires teaching-capable staff.');
            }
        }];
    }
}

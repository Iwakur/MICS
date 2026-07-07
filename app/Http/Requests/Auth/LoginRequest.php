<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Login form request.
 *
 * This request owns both validation and the actual login attempt. Keeping that
 * logic here prevents the controller from turning into a long auth script.
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * These are the two credential fields currently supported by MICS HUB.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Use direct user-facing wording instead of generic validation defaults.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Enter your username.',
            'password.required' => 'Enter your password.',
        ];
    }

    /**
     * Authenticate the incoming credentials.
     *
     * The inactive-user rule is a business rule on top of normal Laravel auth:
     * even valid credentials should not open a session when the account has
     * been disabled by an administrator.
     */
    public function authenticate(): void
    {
        $credentials = $this->safe()->only(['username', 'password']);

        $user = User::query()
            ->where('username', $credentials['username'])
            ->first();

        if ($user !== null && ! $user->is_active) {
            throw ValidationException::withMessages([
                'username' => 'This account is inactive.',
            ]);
        }

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            throw ValidationException::withMessages([
                'username' => trans('auth.failed'),
            ]);
        }
    }
}

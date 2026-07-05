<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReconcileBankMonthRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'date_format:Y-m'],
            'closing_balance' => ['required', 'numeric'],
            'variance_reason' => ['nullable', 'string', 'min:10', 'max:2000'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

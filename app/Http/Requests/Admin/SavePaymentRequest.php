<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReviewStatus;
use App\Models\Payment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SavePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $payment = $this->route('payment');

        return $this->user()?->isAdmin() === true
            && (! $payment instanceof Payment || $payment->status === ReviewStatus::Draft);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:students,id'],
            'month' => ['required', 'date_format:Y-m'],
            'paid_at' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['required', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

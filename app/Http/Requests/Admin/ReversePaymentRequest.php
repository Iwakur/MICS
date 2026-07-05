<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReviewStatus;
use App\Models\Payment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReversePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $payment = $this->route('payment');

        return $this->user()?->isAdmin() === true
            && $payment instanceof Payment
            && $payment->status === ReviewStatus::Validated
            && ! $payment->isReversal()
            && ! $payment->reversal()->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }
}

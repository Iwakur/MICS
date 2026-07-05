<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReviewStatus;
use App\Models\Payment;
use App\Support\Money;
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
            && $payment->refundableCents() > 0;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'gt:0'],
            'reason' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            $payment = $this->route('payment');

            if ($payment instanceof Payment && Money::cents($this->input('amount', 0)) > $payment->refundableCents()) {
                $validator->errors()->add('amount', 'The refund cannot exceed the remaining refundable amount.');
            }
        }];
    }
}

<?php

/**
 * MICS HUB source: app Http Requests Admin SaveExpenseRequest. See docs/file-reference.md for its full responsibility.
 */

namespace App\Http\Requests\Admin;

use App\Enums\ReviewStatus;
use App\Models\BankMonth;
use App\Models\Expense;
use App\Support\Money;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

class SaveExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $expense = $this->route('expense');

        return $this->user()?->isAdmin() === true
            && (! $expense instanceof Expense || $expense->status === ReviewStatus::Draft);
    }

    public function rules(): array
    {
        return [
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'staff_id' => ['nullable', 'exists:staff,id'],
            'month_date' => ['required', 'date_format:Y-m-d'],
            'amount' => ['required', 'numeric', 'min:0'],
            'status' => ['required', new Enum(ReviewStatus::class)],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                if ($this->input('status') === ReviewStatus::Validated->value
                    && BankMonth::query()->whereDate('month_date', $this->date('month_date')?->startOfMonth())->where('status', 'reconciled')->exists()) {
                    $validator->errors()->add('status', __('messages.reopen_bank_before_cash_change'));
                }

                $expense = $this->route('expense');

                if (! $expense instanceof Expense || ! $expense->is_auto_generated) {
                    return;
                }

                $generatedAmount = Money::cents($expense->salarySources()->sum('amount'));
                $submittedAmount = Money::cents($this->input('amount'));

                if ($generatedAmount !== $submittedAmount && ! $this->filled('note')) {
                    $validator->errors()->add('note', 'Explain why the generated salary amount was changed.');
                }
            },
        ];
    }
}

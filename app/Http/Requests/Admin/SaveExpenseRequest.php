<?php

namespace App\Http\Requests\Admin;

use App\Enums\ReviewStatus;
use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

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
}

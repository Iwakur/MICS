<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'staff_id', 'expense_category_id', 'month_date', 'amount', 'status',
    'is_auto_generated', 'note',
])]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    protected $attributes = ['status' => 'draft', 'is_auto_generated' => false];

    protected function casts(): array
    {
        return [
            'month_date' => 'date',
            'amount' => 'decimal:2',
            'status' => ReviewStatus::class,
            'is_auto_generated' => 'boolean',
        ];
    }

    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    #[Scope]
    protected function validated(Builder $query): void
    {
        $query->where('status', ReviewStatus::Validated->value);
    }
}

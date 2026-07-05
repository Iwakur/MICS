<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['student_month_id', 'paid_at', 'amount', 'payment_method', 'status', 'note'])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory;

    protected $attributes = ['status' => 'draft'];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'amount' => 'decimal:2',
            'status' => ReviewStatus::class,
        ];
    }

    public function studentMonth(): BelongsTo
    {
        return $this->belongsTo(StudentMonth::class);
    }

    #[Scope]
    protected function validated(Builder $query): void
    {
        $query->where('status', ReviewStatus::Validated->value);
    }
}

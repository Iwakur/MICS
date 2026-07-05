<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use App\Support\Money;
use Database\Factories\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'student_month_id', 'reversal_of_payment_id', 'paid_at', 'amount', 'payment_method', 'status',
    'validated_by_user_id', 'validated_at', 'note',
])]
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
            'validated_at' => 'datetime',
        ];
    }

    public function studentMonth(): BelongsTo
    {
        return $this->belongsTo(StudentMonth::class);
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_user_id');
    }

    public function reversalOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversal_of_payment_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(self::class, 'reversal_of_payment_id');
    }

    public function refundedCents(): int
    {
        return abs((int) $this->refunds()->where('status', ReviewStatus::Validated)->pluck('amount')->sum(
            fn (string $amount): int => Money::cents($amount),
        ));
    }

    public function refundableCents(): int
    {
        return max(0, Money::cents($this->amount) - $this->refundedCents());
    }

    public function isReversal(): bool
    {
        return $this->reversal_of_payment_id !== null;
    }

    #[Scope]
    protected function validated(Builder $query): void
    {
        $query->where('status', ReviewStatus::Validated->value);
    }
}

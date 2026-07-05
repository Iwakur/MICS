<?php

namespace App\Models;

use App\Enums\ReviewStatus;
use App\Support\Money;
use Database\Factories\StudentMonthFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'student_id', 'month_date', 'lesson_count', 'opening_balance', 'charge_amount',
    'manual_adjustment', 'status', 'validated_by_user_id', 'validated_at',
    'adjustment_reason', 'adjusted_by_user_id', 'note',
])]
class StudentMonth extends Model
{
    /** @use HasFactory<StudentMonthFactory> */
    use HasFactory;

    protected $attributes = [
        'lesson_count' => 0,
        'opening_balance' => 0,
        'charge_amount' => 0,
        'manual_adjustment' => 0,
        'status' => 'draft',
    ];

    protected function casts(): array
    {
        return [
            'month_date' => 'date',
            'lesson_count' => 'integer',
            'opening_balance' => 'decimal:2',
            'charge_amount' => 'decimal:2',
            'manual_adjustment' => 'decimal:2',
            'status' => ReviewStatus::class,
            'validated_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function validatedPayments(): HasMany
    {
        return $this->payments()->where('status', ReviewStatus::Validated);
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by_user_id');
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_user_id');
    }

    public function closingBalance(): float
    {
        return Money::display($this->closingBalanceCents());
    }

    public function adjustedCharge(): float
    {
        return Money::display(Money::cents($this->charge_amount) + Money::cents($this->manual_adjustment));
    }

    public function closingBalanceAmount(): string
    {
        return Money::decimal($this->closingBalanceCents());
    }

    public function closingBalanceCents(): int
    {
        $validatedPaymentAmounts = $this->relationLoaded('validatedPayments')
            ? $this->validatedPayments->pluck('amount')
            : $this->validatedPayments()->pluck('amount');

        $validatedPaymentsCents = $validatedPaymentAmounts
            ->sum(fn (string $amount): int => Money::cents($amount));

        return Money::cents($this->opening_balance)
            + Money::cents($this->charge_amount)
            + Money::cents($this->manual_adjustment)
            - $validatedPaymentsCents;
    }
}

<?php

namespace App\Models;

use Database\Factories\BankMonthFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'month_date', 'opening_balance', 'closing_balance', 'expected_closing_balance', 'status',
    'variance_reason', 'reconciled_by_user_id', 'reconciled_at', 'note',
])]
class BankMonth extends Model
{
    /** @use HasFactory<BankMonthFactory> */
    use HasFactory;

    protected $attributes = [
        'opening_balance' => 0,
        'closing_balance' => 0,
        'expected_closing_balance' => 0,
        'status' => 'draft',
    ];

    protected function casts(): array
    {
        return [
            'month_date' => 'date',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'expected_closing_balance' => 'decimal:2',
            'reconciled_at' => 'datetime',
        ];
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by_user_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(BankMonthEvent::class)->latest('occurred_at');
    }
}

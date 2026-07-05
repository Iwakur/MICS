<?php

namespace App\Models;

use Database\Factories\BillingMonthEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['billing_month_id', 'user_id', 'action', 'reason', 'occurred_at'])]
class BillingMonthEvent extends Model
{
    /** @use HasFactory<BillingMonthEventFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    public function billingMonth(): BelongsTo
    {
        return $this->belongsTo(BillingMonth::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use App\Enums\BillingMonthStatus;
use Database\Factories\BillingMonthFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['month_date', 'status', 'closed_by_user_id', 'closed_at'])]
class BillingMonth extends Model
{
    /** @use HasFactory<BillingMonthFactory> */
    use HasFactory;

    protected $attributes = ['status' => 'open'];

    protected function casts(): array
    {
        return [
            'month_date' => 'date',
            'status' => BillingMonthStatus::class,
            'closed_at' => 'datetime',
        ];
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }
}

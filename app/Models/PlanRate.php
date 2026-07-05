<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['plan_id', 'effective_from', 'lesson_price', 'plan_price', 'teacher_monthly_amount'])]
class PlanRate extends Model
{
    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'lesson_price' => 'decimal:2',
            'plan_price' => 'decimal:2',
            'teacher_monthly_amount' => 'decimal:2',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}

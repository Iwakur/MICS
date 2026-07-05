<?php

namespace App\Models;

use Database\Factories\StudentMonthFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'student_id', 'month_date', 'opening_balance', 'charge_amount',
    'manual_adjustment', 'note',
])]
class StudentMonth extends Model
{
    /** @use HasFactory<StudentMonthFactory> */
    use HasFactory;

    protected $attributes = [
        'opening_balance' => 0,
        'charge_amount' => 0,
        'manual_adjustment' => 0,
    ];

    protected function casts(): array
    {
        return [
            'month_date' => 'date',
            'opening_balance' => 'decimal:2',
            'charge_amount' => 'decimal:2',
            'manual_adjustment' => 'decimal:2',
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
        return $this->payments()->validated();
    }

    public function closingBalance(): float
    {
        $validatedPayments = $this->relationLoaded('validatedPayments')
            ? $this->validatedPayments->sum('amount')
            : $this->validatedPayments()->sum('amount');

        return round(
            (float) $this->opening_balance
            + (float) $this->charge_amount
            + (float) $this->manual_adjustment
            - (float) $validatedPayments,
            2,
        );
    }
}

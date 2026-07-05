<?php

namespace App\Models;

use Database\Factories\BankMonthFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['month_date', 'opening_balance', 'closing_balance', 'note'])]
class BankMonth extends Model
{
    /** @use HasFactory<BankMonthFactory> */
    use HasFactory;

    protected $attributes = ['opening_balance' => 0, 'closing_balance' => 0];

    protected function casts(): array
    {
        return [
            'month_date' => 'date',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
        ];
    }
}

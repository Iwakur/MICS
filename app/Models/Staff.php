<?php

namespace App\Models;

use App\Enums\StaffCompensationMode;
use Database\Factories\StaffFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'staff_role_id', 'first_name', 'family_name', 'father_name', 'email', 'phone',
    'birthday', 'city', 'payout_card_number', 'compensation_mode', 'salary_amount',
    'is_active', 'note',
])]
class Staff extends Model
{
    /** @use HasFactory<StaffFactory> */
    use HasFactory;

    protected $attributes = ['compensation_mode' => 'fixed', 'is_active' => true];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'compensation_mode' => StaffCompensationMode::class,
            'salary_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(StaffRole::class, 'staff_role_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}

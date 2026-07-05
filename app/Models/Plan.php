<?php

namespace App\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'duration_minutes', 'lesson_count', 'lesson_price', 'plan_price',
    'teacher_monthly_amount', 'is_assignable', 'note',
])]
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    protected $attributes = ['is_assignable' => true];

    protected function casts(): array
    {
        return [
            'lesson_price' => 'decimal:2',
            'plan_price' => 'decimal:2',
            'teacher_monthly_amount' => 'decimal:2',
            'is_assignable' => 'boolean',
        ];
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}

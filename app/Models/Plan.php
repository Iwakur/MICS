<?php

namespace App\Models;

use App\Support\EffectiveMonth;
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
            'lesson_count' => 'decimal:1',
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

    public function rates(): HasMany
    {
        return $this->hasMany(PlanRate::class)->orderBy('effective_from');
    }

    public function rateFor(\DateTimeInterface $month): PlanRate
    {
        $rate = $this->rates()->whereDate('effective_from', '<=', $month)->latest('effective_from')->firstOrFail();
        assert($rate instanceof PlanRate);

        return $rate;
    }

    protected static function booted(): void
    {
        static::saved(function (self $plan): void {
            if (! $plan->wasRecentlyCreated && ! $plan->wasChanged(['lesson_price', 'plan_price', 'teacher_monthly_amount'])) {
                return;
            }

            $effectiveFrom = EffectiveMonth::nextEditable();
            $rate = $plan->rates()->whereDate('effective_from', $effectiveFrom)->first();
            $values = ['lesson_price' => $plan->lesson_price, 'plan_price' => $plan->plan_price, 'teacher_monthly_amount' => $plan->teacher_monthly_amount];
            $rate ? $rate->update($values) : $plan->rates()->create($values + ['effective_from' => $effectiveFrom]);
        });
    }
}

<?php

namespace App\Models;

use App\Support\EffectiveMonth;
use Database\Factories\LessonTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'duration_minutes', 'lesson_price', 'teacher_share_per_lesson',
    'is_assignable', 'note',
])]
class LessonType extends Model
{
    /** @use HasFactory<LessonTypeFactory> */
    use HasFactory;

    protected $attributes = ['is_assignable' => true];

    protected function casts(): array
    {
        return [
            'lesson_price' => 'decimal:2',
            'teacher_share_per_lesson' => 'decimal:2',
            'is_assignable' => 'boolean',
        ];
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(LessonTypeRate::class)->orderBy('effective_from');
    }

    public function rateFor(\DateTimeInterface $month): LessonTypeRate
    {
        $rate = $this->rates()->whereDate('effective_from', '<=', $month)->latest('effective_from')->firstOrFail();
        assert($rate instanceof LessonTypeRate);

        return $rate;
    }

    protected static function booted(): void
    {
        static::saved(function (self $lessonType): void {
            if (! $lessonType->wasRecentlyCreated && ! $lessonType->wasChanged(['lesson_price', 'teacher_share_per_lesson'])) {
                return;
            }

            $effectiveFrom = EffectiveMonth::nextEditable();
            $rate = $lessonType->rates()->whereDate('effective_from', $effectiveFrom)->first();
            $values = ['lesson_price' => $lessonType->lesson_price, 'teacher_share_per_lesson' => $lessonType->teacher_share_per_lesson];
            $rate ? $rate->update($values) : $lessonType->rates()->create($values + ['effective_from' => $effectiveFrom]);
        });
    }
}

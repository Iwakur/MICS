<?php

namespace App\Models;

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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lesson_type_id', 'effective_from', 'lesson_price', 'teacher_share_per_lesson'])]
class LessonTypeRate extends Model
{
    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'lesson_price' => 'decimal:2',
            'teacher_share_per_lesson' => 'decimal:2',
        ];
    }

    public function lessonType(): BelongsTo
    {
        return $this->belongsTo(LessonType::class);
    }
}

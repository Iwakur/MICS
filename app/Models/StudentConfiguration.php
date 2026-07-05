<?php

namespace App\Models;

use App\Enums\StudentBillingType;
use App\Enums\StudentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'student_id', 'effective_from', 'staff_id', 'status', 'billing_type', 'lesson_type_id',
    'lesson_amount', 'plan_id', 'plan_start_at', 'discount_amount',
])]
class StudentConfiguration extends Model
{
    protected $attributes = ['status' => 'active', 'discount_amount' => 0];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'status' => StudentStatus::class,
            'billing_type' => StudentBillingType::class,
            'plan_start_at' => 'date',
            'discount_amount' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function lessonType(): BelongsTo
    {
        return $this->belongsTo(LessonType::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}

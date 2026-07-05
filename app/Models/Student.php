<?php

namespace App\Models;

use App\Enums\StudentBillingType;
use App\Enums\StudentStatus;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'staff_id', 'first_name', 'family_name', 'father_name', 'email', 'phone',
    'birthday', 'city', 'joined_at', 'status', 'billing_type', 'lesson_type_id',
    'lesson_amount', 'plan_id', 'plan_start_at', 'discount_amount', 'note',
])]
class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    protected $attributes = ['status' => 'active', 'discount_amount' => 0];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'joined_at' => 'date',
            'plan_start_at' => 'date',
            'status' => StudentStatus::class,
            'billing_type' => StudentBillingType::class,
            'discount_amount' => 'decimal:2',
        ];
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

    public function months(): HasMany
    {
        return $this->hasMany(StudentMonth::class);
    }
}

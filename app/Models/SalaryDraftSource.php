<?php

/**
 * MICS HUB source: app Models SalaryDraftSource. See docs/file-reference.md for its full responsibility.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['expense_id', 'student_id', 'source_type', 'description', 'units', 'rate', 'amount'])]
class SalaryDraftSource extends Model
{
    protected function casts(): array
    {
        return [
            'units' => 'decimal:2',
            'rate' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}

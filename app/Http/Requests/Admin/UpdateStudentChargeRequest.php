<?php

/**
 * MICS HUB source: app Http Requests Admin UpdateStudentChargeRequest. See docs/file-reference.md for its full responsibility.
 */

namespace App\Http\Requests\Admin;

use App\Enums\ReviewStatus;
use App\Models\StudentMonth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateStudentChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $studentMonth = $this->route('studentMonth');

        return $this->user()?->isAdmin() === true
            && $studentMonth instanceof StudentMonth
            && $studentMonth->status === ReviewStatus::Draft;
    }

    public function rules(): array
    {
        return [
            'manual_adjustment' => ['required', 'numeric'],
            'adjustment_reason' => ['required', 'string', 'max:2000'],
            'status' => ['required', new Enum(ReviewStatus::class)],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

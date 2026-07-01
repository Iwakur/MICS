<?php

declare(strict_types=1);

namespace App\Services;

final class PlanFormService
{
    public const ASSIGNABLE_OPTIONS = ['assignable', 'archived'];

    public function validate(array $input, bool $nameExists): array
    {
        $errors = [];
        $name = trim((string) ($input['name'] ?? ''));
        $lessonCountRaw = trim((string) ($input['lesson_count'] ?? ''));
        $lessonPriceRaw = trim((string) ($input['lesson_price'] ?? ''));
        $teacherShareRaw = trim((string) ($input['teacher_share_per_lesson'] ?? ''));
        $assignable = trim((string) ($input['assignable_state'] ?? 'assignable'));
        $comments = trim((string) ($input['comments'] ?? ''));

        if ($name === '') {
            $errors['name'] = 'Plan name is required.';
        } elseif ($nameExists) {
            $errors['name'] = 'Plan name must be unique.';
        }

        if (! is_numeric($lessonCountRaw) || (float) $lessonCountRaw < 0) {
            $errors['lesson_count'] = 'Lesson count must be zero or greater.';
        }

        if (! is_numeric($lessonPriceRaw) || (float) $lessonPriceRaw < 0) {
            $errors['lesson_price'] = 'Lesson price must be zero or greater.';
        }

        if (! is_numeric($teacherShareRaw) || (float) $teacherShareRaw < 0) {
            $errors['teacher_share_per_lesson'] = 'Teacher share per lesson must be zero or greater.';
        }

        if (! in_array($assignable, self::ASSIGNABLE_OPTIONS, true)) {
            $errors['assignable_state'] = 'Choose a valid plan status.';
        }

        return [
            'errors' => $errors,
            'data' => [
                'name' => $name,
                'lesson_count' => round((float) $lessonCountRaw, 2),
                'lesson_price' => round((float) $lessonPriceRaw, 2),
                'teacher_share_per_lesson' => round((float) $teacherShareRaw, 2),
                'is_assignable' => $assignable === 'assignable',
                'comments' => $comments !== '' ? $comments : null,
            ],
        ];
    }

    public function defaults(?array $plan = null): array
    {
        return [
            'name' => (string) old('name', (string) ($plan['name'] ?? '')),
            'lesson_count' => (string) old('lesson_count', isset($plan['lesson_count']) ? (string) $plan['lesson_count'] : '0'),
            'lesson_price' => (string) old('lesson_price', isset($plan['lesson_price']) ? (string) $plan['lesson_price'] : '0'),
            'teacher_share_per_lesson' => (string) old('teacher_share_per_lesson', isset($plan['teacher_share_per_lesson']) ? (string) $plan['teacher_share_per_lesson'] : '0'),
            'assignable_state' => (string) old(
                'assignable_state',
                (($plan['is_assignable'] ?? true) ? 'assignable' : 'archived')
            ),
            'comments' => (string) old('comments', (string) ($plan['comments'] ?? '')),
        ];
    }
}

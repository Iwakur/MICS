<?php

namespace App\Services;

use App\Enums\StudentBillingType;
use App\Enums\StudentStatus;
use App\Models\StudentConfiguration;
use Carbon\CarbonImmutable;

class DashboardStudentStatistics
{
    /** @return array{total: int, active: int, paused: int, archived: int, per_lesson: int, plan_based: int} */
    public function forMonth(CarbonImmutable $month): array
    {
        $statistics = StudentConfiguration::query()
            ->join('students', 'students.id', '=', 'student_configurations.student_id')
            ->whereDate('students.joined_at', '<=', $month->endOfMonth())
            ->whereDate('student_configurations.effective_from', '<=', $month)
            ->whereNotExists(function ($query) use ($month): void {
                $query->selectRaw('1')
                    ->from('student_configurations as newer_configurations')
                    ->whereColumn('newer_configurations.student_id', 'student_configurations.student_id')
                    ->whereColumn('newer_configurations.effective_from', '>', 'student_configurations.effective_from')
                    ->whereDate('newer_configurations.effective_from', '<=', $month);
            })
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN student_configurations.status = ? THEN 1 ELSE 0 END) as active', [StudentStatus::Active->value])
            ->selectRaw('SUM(CASE WHEN student_configurations.status = ? THEN 1 ELSE 0 END) as paused', [StudentStatus::Paused->value])
            ->selectRaw('SUM(CASE WHEN student_configurations.status = ? THEN 1 ELSE 0 END) as archived', [StudentStatus::Archived->value])
            ->selectRaw('SUM(CASE WHEN student_configurations.billing_type = ? THEN 1 ELSE 0 END) as per_lesson', [StudentBillingType::PerLesson->value])
            ->selectRaw('SUM(CASE WHEN student_configurations.billing_type = ? THEN 1 ELSE 0 END) as plan_based', [StudentBillingType::PlanBased->value])
            ->firstOrFail();

        return [
            'total' => (int) $statistics->getAttribute('total'),
            'active' => (int) $statistics->getAttribute('active'),
            'paused' => (int) $statistics->getAttribute('paused'),
            'archived' => (int) $statistics->getAttribute('archived'),
            'per_lesson' => (int) $statistics->getAttribute('per_lesson'),
            'plan_based' => (int) $statistics->getAttribute('plan_based'),
        ];
    }
}

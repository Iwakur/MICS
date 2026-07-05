<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\StudentBillingType;
use App\Enums\StudentStatus;
use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Teacher dashboard controller.
 *
 * The teacher dashboard intentionally starts smaller than the admin dashboard.
 * That difference teaches the product rule that role scope changes the visible
 * workspace, not only the authorization checks behind the scenes.
 */
class TeacherDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $staffId = $request->user()->staff_id;

        return view('teacher.dashboard', [
            'activeStudents' => $staffId ? Student::query()->where('staff_id', $staffId)->where('status', StudentStatus::Active->value)->count() : 0,
            'perLessonStudents' => $staffId ? Student::query()->where('staff_id', $staffId)->where('billing_type', StudentBillingType::PerLesson->value)->count() : 0,
            'planStudents' => $staffId ? Student::query()->where('staff_id', $staffId)->where('billing_type', StudentBillingType::PlanBased->value)->count() : 0,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StaffCompensationMode;
use App\Http\Controllers\Controller;
use App\Models\LessonType;
use App\Models\Plan;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use Illuminate\View\View;

/**
 * Administrator dashboard controller.
 *
 * The admin home provides grouped operational counts and entry points into
 * people, access, catalog, and monthly finance workflows.
 */
class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::query()->count(),
            'totalStaff' => Staff::query()->count(),
            'fixedStaff' => Staff::query()->where('compensation_mode', StaffCompensationMode::Fixed->value)->count(),
            'dynamicStaff' => Staff::query()->where('compensation_mode', StaffCompensationMode::Dynamic->value)->count(),
            'totalLessonTypes' => LessonType::query()->count(),
            'totalPlans' => Plan::query()->count(),
            'totalStudents' => Student::query()->count(),
        ]);
    }
}

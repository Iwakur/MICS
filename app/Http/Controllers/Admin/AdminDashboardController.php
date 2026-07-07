<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StaffCompensationMode;
use App\Http\Controllers\Controller;
use App\Models\LessonType;
use App\Models\Plan;
use App\Models\Staff;
use App\Models\User;
use App\Services\DashboardStudentStatistics;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Administrator dashboard controller.
 *
 * The admin home provides grouped operational counts and entry points into
 * people, access, catalog, and monthly finance workflows.
 */
class AdminDashboardController extends Controller
{
    public function __invoke(Request $request, DashboardStudentStatistics $studentStatistics): View
    {
        $month = $request->string('month', now()->format('Y-m'))->toString();
        abort_unless(preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) === 1, 404);
        $month = CarbonImmutable::createFromFormat('!Y-m', $month);

        return view('admin.dashboard', [
            'month' => $month,
            'currentMonth' => CarbonImmutable::now()->startOfMonth(),
            'studentStatistics' => $studentStatistics->forMonth($month),
            'totalUsers' => User::query()->count(),
            'totalStaff' => Staff::query()->count(),
            'fixedStaff' => Staff::query()->where('compensation_mode', StaffCompensationMode::Fixed->value)->count(),
            'dynamicStaff' => Staff::query()->where('compensation_mode', StaffCompensationMode::Dynamic->value)->count(),
            'totalLessonTypes' => LessonType::query()->count(),
            'totalPlans' => Plan::query()->count(),
        ]);
    }
}

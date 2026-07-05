<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

/**
 * Administrator dashboard controller.
 *
 * The first admin home is intentionally simple: it gives a quick operational
 * summary and an entry point into user management.
 */
class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalUsers' => User::query()->count(),
            'activeAdmins' => User::query()
                ->where('role', UserRole::Admin->value)
                ->where('is_active', true)
                ->count(),
            'activeTeachers' => User::query()
                ->where('role', UserRole::Teacher->value)
                ->where('is_active', true)
                ->count(),
        ]);
    }
}

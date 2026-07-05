<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

/**
 * Role-aware dashboard entry point.
 *
 * Users never stay on /dashboard itself. The route exists as a clean shared
 * entry so the login flow can redirect to one place and then branch by role.
 */
class DashboardController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $user = auth()->user();

        return $user->isAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('teacher.dashboard');
    }
}

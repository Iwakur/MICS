<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

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

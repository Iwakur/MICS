<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
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
    public function __invoke(): View
    {
        return view('teacher.dashboard');
    }
}

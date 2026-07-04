<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TeacherDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('teacher.dashboard');
    }
}

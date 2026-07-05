<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BankMonthController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\FinanceSummaryController;
use App\Http\Controllers\Admin\LessonTypeController;
use App\Http\Controllers\Admin\MonthClosingController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StaffRoleController;
use App\Http\Controllers\Admin\StudentChargeController;
use App\Http\Controllers\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonthlyLessonCountController;
use App\Http\Controllers\ReadinessController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::get('/ready', ReadinessController::class)->name('ready');

/*
|--------------------------------------------------------------------------
| Browser routes
|--------------------------------------------------------------------------
|
| This file defines the public and authenticated browser surface for the app.
| The flow is intentionally small right now: guests may only reach login,
| authenticated users enter through /dashboard, and that route then dispatches
| them to the correct role-specific area.
|
*/
Route::get('/', function (): RedirectResponse {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Guest-only auth routes
|--------------------------------------------------------------------------
|
| These routes exist only for users who are not yet authenticated. The login
| POST is throttled because auth endpoints are a natural brute-force target.
|
*/
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated application routes
|--------------------------------------------------------------------------
|
| /dashboard is not a final page. It is a role-aware entry point that sends
| administrators and teachers to different dashboards. Admin routes sit behind
| both auth and the custom admin middleware alias.
|
*/
Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function (): void {
        Route::get('/dashboard', AdminDashboardController::class)
            ->name('dashboard');

        Route::resource('staff', StaffController::class)
            ->except('show');

        Route::resource('staff-roles', StaffRoleController::class)
            ->except('show');

        Route::resource('lesson-types', LessonTypeController::class)
            ->except('show');

        Route::resource('plans', PlanController::class)
            ->except('show');

        Route::resource('students', AdminStudentController::class)
            ->except('show');

        Route::get('lesson-counts', [MonthlyLessonCountController::class, 'index'])->name('lesson-counts.index');
        Route::put('lesson-counts', [MonthlyLessonCountController::class, 'update'])->name('lesson-counts.update');
        Route::get('month-closing', [MonthClosingController::class, 'index'])->name('month-closing.index');
        Route::post('month-closing', [MonthClosingController::class, 'store'])->name('month-closing.store');
        Route::post('month-closing/reopen', [MonthClosingController::class, 'reopen'])->name('month-closing.reopen');
        Route::post('payments/{payment}/validate', [PaymentController::class, 'validatePayment'])->name('payments.validate');
        Route::post('payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse');
        Route::resource('payments', PaymentController::class)->except('show');
        Route::resource('expenses', ExpenseController::class)->except('show');
        Route::resource('expense-categories', ExpenseCategoryController::class)->except('show');
        Route::get('bank-months', [BankMonthController::class, 'index'])->name('bank-months.index');
        Route::post('bank-months', [BankMonthController::class, 'store'])->name('bank-months.store');
        Route::post('bank-months/{bankMonth}/reopen', [BankMonthController::class, 'reopen'])->name('bank-months.reopen');
        Route::get('finance-summary', FinanceSummaryController::class)->name('finance-summary');
        Route::get('student-charges', [StudentChargeController::class, 'index'])->name('student-charges.index');
        Route::get('student-charges/{studentMonth}/edit', [StudentChargeController::class, 'edit'])->name('student-charges.edit');
        Route::put('student-charges/{studentMonth}', [StudentChargeController::class, 'update'])->name('student-charges.update');

        Route::resource('users', UserController::class)
            ->except('show');
    });

    Route::get('/teacher/dashboard', TeacherDashboardController::class)
        ->name('teacher.dashboard');

    Route::prefix('teacher')->name('teacher.')->group(function (): void {
        Route::resource('students', TeacherStudentController::class)
            ->only(['index', 'create', 'store', 'edit', 'update']);
        Route::get('lesson-counts', [MonthlyLessonCountController::class, 'index'])->name('lesson-counts.index');
        Route::put('lesson-counts', [MonthlyLessonCountController::class, 'update'])->name('lesson-counts.update');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

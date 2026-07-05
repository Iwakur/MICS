<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

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
Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function (): void {
        Route::get('/dashboard', AdminDashboardController::class)
            ->name('dashboard');

        Route::resource('users', UserController::class)
            ->except('show');
    });

    Route::get('/teacher/dashboard', TeacherDashboardController::class)
        ->name('teacher.dashboard');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

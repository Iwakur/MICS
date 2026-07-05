<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Session lifecycle controller.
 *
 * This controller stays intentionally thin: the request object owns the actual
 * credential validation/authentication work, while the controller coordinates
 * which view or redirect the browser should receive.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login form to guests.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Authenticate the submitted credentials and start a fresh session.
     *
     * Session regeneration is important because it prevents session fixation
     * after a successful login.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * End the current browser session and send the user back to login.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

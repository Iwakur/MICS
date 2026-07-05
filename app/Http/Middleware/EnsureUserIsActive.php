<?php

namespace App\Http\Middleware;

use App\Models\Staff;
use App\Models\StaffRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Revokes sessions for accounts deactivated after they authenticated.
 */
class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $staff = $user?->staffMember?->loadMissing('role');
        $invalidProfile = false;
        if ($user?->is_active === true) {
            $invalidProfile = ! $staff instanceof Staff
                || ! $staff->is_active
                || ! $staff->role instanceof StaffRole
                || ! $staff->role->is_active
                || ($user->isTeacher() && ! $staff->role->can_teach);
        }

        if ($user?->is_active === false || $invalidProfile) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return to_route('login')->withErrors([
                'username' => $invalidProfile
                    ? 'This active account requires a valid active staff profile.'
                    : 'This account is inactive.',
            ]);
        }

        return $next($request);
    }
}

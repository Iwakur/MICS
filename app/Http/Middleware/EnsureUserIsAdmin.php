<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin-only browser middleware.
 *
 * This keeps role checks out of individual admin controller actions. Routes can
 * stay readable by saying "middleware('admin')" instead of repeating the same
 * guard logic in every endpoint.
 */
class EnsureUserIsAdmin
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isAdmin(), 403);

        return $next($request);
    }
}

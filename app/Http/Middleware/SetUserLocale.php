<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $locale = $user instanceof User ? $user->locale : config('app.locale');

        if (array_key_exists($locale, (array) config('app.supported_locales'))) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}

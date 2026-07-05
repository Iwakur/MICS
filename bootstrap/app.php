<?php

use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Laravel application bootstrap
|--------------------------------------------------------------------------
|
| Laravel 13 registers route files, middleware aliases, and exception behavior
| here instead of in the older Http\Kernel structure. This repository keeps a
| custom admin alias here so browser routes can express admin access clearly.
|
*/
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * The admin alias protects the admin dashboard and user-management
         * routes without hardcoding controller checks into every action.
         */
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
         * API routes should naturally prefer JSON rendering when they are added.
         * The current app is browser-first, but this keeps future API behavior
         * pointed in the right direction.
         */
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

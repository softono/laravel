<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Kept in its own file so the "replace the old auth" diff
            // against routes/web.php stays obvious.
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(__DIR__.'/../routes/auth.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\EnsureDeviceUid::class,
        ]);

        $middleware->alias([
            // Legacy auth (removed once the cutover in Phase 7 lands):
            'user' => \App\Http\Middleware\UserAuth::class,
            'admin' => \App\Http\Middleware\AdminAuth::class,

            // Next-parity auth:
            'device.uid' => \App\Http\Middleware\EnsureDeviceUid::class,
            'auth.session' => \App\Http\Middleware\AuthenticateSession::class,
            'guest.redirect' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'auth.throttle' => \App\Http\Middleware\AuthRateLimit::class,
            'admin.session' => \App\Http\Middleware\AuthenticateAdminSession::class,
            'admin.guest.redirect' => \App\Http\Middleware\RedirectIfAdminAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

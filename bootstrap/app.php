<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AuthenticateAdminSession;
use App\Http\Middleware\AuthenticateSession;
use App\Http\Middleware\AuthRateLimit;
use App\Http\Middleware\EnsureDeviceUid;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\UserAuth;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Kept in its own file so the "replace the old auth" diff
            // against routes/web.php stays obvious.
            Route::middleware('web')
                ->group(__DIR__.'/../routes/auth.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            EnsureDeviceUid::class,
        ]);

        $middleware->alias([
            // Legacy auth (removed once the cutover in Phase 7 lands):
            'user' => UserAuth::class,
            'admin' => AdminAuth::class,

            // Next-parity auth:
            'device.uid' => EnsureDeviceUid::class,
            'auth.session' => AuthenticateSession::class,
            'guest.redirect' => RedirectIfAuthenticated::class,
            'auth.throttle' => AuthRateLimit::class,
            'admin.session' => AuthenticateAdminSession::class,
            'admin.guest.redirect' => RedirectIfAdminAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

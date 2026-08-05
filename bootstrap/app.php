<?php

use App\Http\Middleware\AuthenticateAdminSession;
use App\Http\Middleware\AuthenticateSession;
use App\Http\Middleware\AuthRateLimit;
use App\Http\Middleware\EnsureDeviceUid;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\StorageApiAuth;
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

            // S3-compatible REST API - deliberately NOT under Laravel's
            // usual /api prefix, since real S3 clients expect bucket/object
            // operations at the host root (GET /, PUT /{bucket}, etc. - see
            // S3 Compatible APIs in docs/local/prd.md). Registered last so
            // its catch-all {bucket}/{object} patterns never shadow the
            // static admin/auth routes above (see docs/local/api.md for the
            // resulting list of reserved top-level bucket names).
            Route::group(['middleware' => ['storage.auth', 'throttle:storage-api']], function () {
                require __DIR__.'/../routes/storage_api.php';
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            EnsureDeviceUid::class,
        ]);

        $middleware->alias([
            'device.uid' => EnsureDeviceUid::class,
            'auth.user' => AuthenticateSession::class,
            'auth.redirect' => RedirectIfAuthenticated::class,
            'auth.throttle' => AuthRateLimit::class,
            'auth.admin' => AuthenticateAdminSession::class,
            'admin.guest.redirect' => RedirectIfAdminAuthenticated::class,
            'storage.auth' => StorageApiAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

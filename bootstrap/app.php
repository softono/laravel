<?php

use App\Helpers\Response;
use App\Http\Middleware\AuthenticateAdminSession;
use App\Http\Middleware\AuthenticateSession;
use App\Http\Middleware\AuthRateLimit;
use App\Http\Middleware\EnsureDeviceUid;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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

            // Modular routes (docs/local/module_structure.md). Each module owns a
            // <module>_routes.php next to its controllers.
            foreach (glob(app_path('Modules/*/*_routes.php')) as $file) {
                Route::middleware('web')->group($file);
            }

            // Admin auth (login and password pages): admin prefix, guests only, so each
            // route sets its own middleware.
            foreach (glob(app_path('Modules/Admin/Auth/*_routes.php')) as $file) {
                Route::middleware('web')->prefix('admin')->group($file);
            }

            // Every other admin module: admin prefix and an admin session are applied
            // here, so a new admin module cannot forget auth.admin.
            foreach (glob(app_path('Modules/Admin/*/*_routes.php')) as $file) {
                if (basename(dirname($file)) === 'Auth') {
                    continue;
                }
                Route::middleware(['web', 'auth.admin'])->prefix('admin')->group($file);
            }
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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // AJAX callers always get the {status, message, data} envelope, including for
        // inline $request->validate() failures, which otherwise use Laravel's own 422 body.
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->ajax()) {
                return Response::sendError(422, $e->validator->errors()->first());
            }
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() === 419 && ($request->expectsJson() || $request->ajax())) {
                return Response::sendError(419, 'Your session has expired. Please refresh the page and try again.');
            }
        });
    })->create();

<?php

use App\Helpers\Response;
use App\Http\Middleware\AuthenticateAdminSession;
use App\Http\Middleware\AuthenticateSession;
use App\Http\Middleware\AuthRateLimit;
use App\Http\Middleware\EnsureDeviceUid;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\VerifyRecaptcha;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
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
        // Behind a reverse proxy the scheme and host come from X-Forwarded-*; without this, generated
        // URLs are http:// on an https site. TRUSTED_PROXIES: `*`, or a comma-separated list of proxy IPs.
        $middleware->trustProxies(
            at: env('TRUSTED_PROXIES', '*') === '*' ? '*' : array_map('trim', explode(',', env('TRUSTED_PROXIES'))),
            headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO,
        );

        // Read by the server to render the visitor's colour theme; JS sets them (see docs/frontend.md), so they are plain cookies.
        $middleware->encryptCookies(except: ['app-theme', 'app-color-mode', 'app-system-prefers-dark', 'sidebar_state']);

        $middleware->web(append: [
            EnsureDeviceUid::class,
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'device.uid' => EnsureDeviceUid::class,
            'auth.user' => AuthenticateSession::class,
            'auth.redirect' => RedirectIfAuthenticated::class,
            'auth.throttle' => AuthRateLimit::class,
            'auth.admin' => AuthenticateAdminSession::class,
            'recaptcha' => VerifyRecaptcha::class,
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
            $messages = [
                419 => 'Your session has expired. Please refresh the page and try again.',
                429 => 'Too many requests. Please try again later.',
            ];

            if (isset($messages[$e->getStatusCode()]) && ($request->expectsJson() || $request->ajax())) {
                return Response::sendError($e->getStatusCode(), $messages[$e->getStatusCode()]);
            }
        });
    })->create();

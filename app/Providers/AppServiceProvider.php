<?php

namespace App\Providers;

use App\Helpers\SessionTokenGuard;
use App\Services\Auth\SessionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Backs the 'web' guard with the signed session cookie instead of
        // Laravel's own session-based auth, so auth()->user(), Auth::id()
        // and @auth keep working across the whole app.
        Auth::extend('session_token', function ($app, $name, array $config) {
            return new SessionTokenGuard($app->make(SessionService::class), $app['request']);
        });
    }
}

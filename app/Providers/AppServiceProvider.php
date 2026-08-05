<?php

namespace App\Providers;

use App\Helpers\SessionTokenGuard;
use App\Services\Auth\SessionService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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

        // Storage S3 API rate limiting - keyed on the authenticated access
        // key, falling back to IP for unauthenticated public-bucket GET/HEAD
        // requests. See Rate Limiting in docs/local/prd.md. Must run behind
        // StorageApiAuth (see routes/api.php) so storage_api_user is set.
        RateLimiter::for('storage-api', function (Request $request) {
            $apiUser = $request->attributes->get('storage_api_user');
            $key = $apiUser ? 'access-key:'.$apiUser->access_key : 'ip:'.$request->ip();
            $limit = (int) config('setting.storage_rate_limit_per_minute', 60);

            return Limit::perMinute($limit)->by($key);
        });
    }
}

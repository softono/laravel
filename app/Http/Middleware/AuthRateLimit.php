<?php

namespace App\Http\Middleware;

use App\Helpers\ClientInfo;
use App\Helpers\Response;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Rate limits auth endpoints. Deliberately NOT Laravel's built-in
 * `throttle` middleware, which returns an HTML/ThrottleRequestsException
 * body shape - these routes must always return the {status,message,data}
 * envelope. Fails closed: a cache failure here also returns 429, not 200.
 *
 * Usage: ->middleware('auth.throttle:login') etc. `$name` selects the
 * limit pair [maxAttempts, decaySeconds] below.
 */
class AuthRateLimit
{
    /** @var array<string,array{0:int,1:int}> name => [maxAttempts, decaySeconds] */
    protected const LIMITS = [
        'login' => [10, 900],
        'admin_login' => [10, 900],
        'register' => [5, 900],
        'otp' => [5, 900],
        'forgot_password' => [5, 900],
        'reset_password' => [5, 900],
        'verify_account' => [5, 900],
        'login_otp' => [5, 900],
        'tfa' => [5, 900],
        'login_link' => [5, 300],
        'login_link_poll' => [900, 300],
        'login_link_approve' => [20, 300],
    ];

    public function handle(Request $request, Closure $next, string $name)
    {
        [$max, $decay] = self::LIMITS[$name] ?? [10, 900];

        try {
            $key = $this->key($request, $name);

            if (RateLimiter::tooManyAttempts($key, $max)) {
                $retryAfter = RateLimiter::availableIn($key);

                return $this->tooManyAttempts($retryAfter, $max);
            }

            RateLimiter::hit($key, $decay);
        } catch (\Throwable $e) {
            // failClosed: a broken rate-limit store must not silently
            // allow unlimited attempts through.
            Log::error('AuthRateLimit: cache failure, failing closed', ['error' => $e->getMessage()]);

            return $this->tooManyAttempts($decay, $max);
        }

        return $next($request);
    }

    protected function key(Request $request, string $name): string
    {
        if ($name === 'login_link_poll') {
            return 'login-link:poll:'.$request->input('request_id').':'.ClientInfo::ip($request);
        }

        return $name.':'.$request->path().':'.ClientInfo::ip($request);
    }

    protected function tooManyAttempts(int $retryAfter, int $max)
    {
        $response = Response::sendError(429, 'Too many requests. Please try again later.');

        return $response->withHeaders([
            'Retry-After' => $retryAfter,
            'X-RateLimit-Limit' => $max,
            'X-RateLimit-Remaining' => 0,
            'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->timestamp,
        ]);
    }
}

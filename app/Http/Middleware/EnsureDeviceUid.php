<?php

namespace App\Http\Middleware;

use App\Helpers\SignedCookie;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Runs on every web request; if the device_uid cookie is missing,
 * generates one and:
 *  - stashes it on the request attributes so the CURRENT request (e.g. a
 *    login happening right now) can already record it against a device,
 *  - queues a 1-year cookie for future requests.
 */
class EnsureDeviceUid
{
    public function handle(Request $request, Closure $next)
    {
        $cookieName = SignedCookie::name('device_uid');
        $uid = $request->cookie($cookieName);

        if (! $uid) {
            $uid = Str::random(64);
            $request->attributes->set('device_uid', $uid);
            SignedCookie::queueRaw('device_uid', $uid, config('auth_next.device_cookie_days') * 86400);
        }

        return $next($request);
    }
}

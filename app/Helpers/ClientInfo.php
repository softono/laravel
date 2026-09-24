<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use WhichBrowser\Parser;

/**
 * Client IP, user agent and device UID extraction shared by every auth flow.
 */
class ClientInfo
{
    /**
     * Real client IP. Trusts exactly `trusted_proxy_count` hops from the right of
     * X-Forwarded-For: a client can prepend fake entries to that header but cannot
     * touch the ones appended by the proxies in front of this app.
     */
    public static function ip(Request $request): ?string
    {
        $hops = max(1, (int) config('auth_next.trusted_proxy_count'));
        $forwarded = array_values(array_filter(array_map('trim', explode(',', (string) $request->header('X-Forwarded-For')))));

        if (count($forwarded) >= $hops) {
            $candidate = $forwarded[count($forwarded) - $hops];

            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }

        $realIp = $request->header('X-Real-IP');
        if ($realIp && filter_var($realIp, FILTER_VALIDATE_IP)) {
            return $realIp;
        }

        return $request->ip();
    }

    public static function userAgent(Request $request): ?string
    {
        $ua = $request->userAgent();

        return $ua ? substr($ua, 0, 512) : null;
    }

    /**
     * Friendly device name from the user agent, e.g. "Chrome on Windows".
     */
    public static function deviceName(Request $request): string
    {
        return self::deviceNameFor($request->userAgent());
    }

    /** "Chrome on Windows" from a stored user agent string (session and activity lists). */
    public static function deviceNameFor(?string $userAgent): string
    {
        if (! $userAgent) {
            return '';
        }

        $result = new Parser($userAgent);

        if (isset($result->browser->name)) {
            return trim($result->browser->name.' on '.($result->os->name ?? ''));
        }

        return '';
    }

    /**
     * The device_uid cookie value, resolved either from the cookie itself
     * or the request attribute EnsureDeviceUid stashes for the current
     * request (needed when the cookie is being set for the first time in
     * this very request - e.g. a login that should record a device_uid
     * before the browser has seen the Set-Cookie response).
     */
    public static function deviceUid(Request $request): ?string
    {
        $name = SignedCookie::name('device_uid');

        return $request->attributes->get('device_uid') ?? $request->cookie($name);
    }
}

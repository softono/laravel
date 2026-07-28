<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use WhichBrowser\Parser;

/**
 * Port of the Next app's src/server/utils/clientInfo.ts - client IP,
 * user agent and device UID extraction shared by every auth flow.
 */
class ClientInfo
{
    /**
     * Real client IP: walk X-Forwarded-For right-to-left skipping
     * private/reserved ranges, then fall back to X-Real-IP, then the
     * framework's own resolved IP.
     */
    public static function ip(Request $request): ?string
    {
        $forwarded = $request->header('X-Forwarded-For');

        if ($forwarded) {
            $ips = array_reverse(array_map('trim', explode(',', $forwarded)));

            foreach ($ips as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
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
        $ua = $request->userAgent();

        if (! $ua) {
            return '';
        }

        $result = new Parser($ua);

        if ($result && isset($result->browser->name)) {
            return trim(($result->browser->name ?? '').' on '.($result->os->name ?? ''));
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

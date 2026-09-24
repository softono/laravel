<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * "City, Region, Country" for an IP, from ipapi.co like Next's `getIpLocation`.
 * The lookup sits on a login path, so it is best-effort: a short timeout, no
 * exception, and a remembered miss so an unreachable service is not retried on
 * every request.
 */
class IpLocationService
{
    public const UNKNOWN = 'Unknown';

    public function lookup(string $ip): string
    {
        // Loopback and private ranges have no location; do not send them to a third party.
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return self::UNKNOWN;
        }

        return Cache::remember('ip_location:'.$ip, now()->addDay(), fn () => $this->fetch($ip) ?? self::UNKNOWN);
    }

    protected function fetch(string $ip): ?string
    {
        try {
            $response = Http::timeout(3)->acceptJson()->get('https://ipapi.co/'.$ip.'/json/');
        } catch (\Throwable) {
            return null;
        }

        if (! $response->ok()) {
            return null;
        }

        $parts = array_filter([
            $response->json('city'),
            $response->json('region'),
            $response->json('country_name') ?: $response->json('country'),
        ]);

        return $parts ? implode(', ', $parts) : null;
    }
}

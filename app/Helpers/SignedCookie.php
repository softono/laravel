<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

/**
 * signing scheme.
 *
 * Signing = payload . '.' . base64url(hmac_sha256(payload, key)),
 * verified with hash_equals (constant-time). All auth cookies are
 * HttpOnly, Path=/, SameSite=Lax, Secure in production, and prefixed
 * with the app UID (config('setting.app_uid') falling back to
 * config('auth_next.app_uid')).
 */
class SignedCookie
{
    public static function name(string $suffix): string
    {
        $uid = config('setting.app_uid') ?: config('auth_next.app_uid');

        return "{$uid}_{$suffix}";
    }

    public static function sign(string $payload): string
    {
        $mac = self::base64UrlEncode(hash_hmac('sha256', $payload, self::key(), true));

        return $payload.'.'.$mac;
    }

    /**
     * Verifies a signed cookie value and returns the original payload,
     * or null if the value is missing, malformed, or the signature
     * doesn't match.
     */
    public static function verify(?string $value): ?string
    {
        if (! $value || ! str_contains($value, '.')) {
            return null;
        }

        $pos = strrpos($value, '.');
        $payload = substr($value, 0, $pos);
        $mac = substr($value, $pos + 1);

        $expected = self::base64UrlEncode(hash_hmac('sha256', $payload, self::key(), true));

        if (! hash_equals($expected, $mac)) {
            return null;
        }

        return $payload;
    }

    /**
     * Queues a signed cookie for the given number of seconds.
     */
    public static function queue(string $suffix, string $payload, int $ttlSeconds): void
    {
        Cookie::queue(self::make($suffix, self::sign($payload), $ttlSeconds));
    }

    /**
     * Queues a raw (unsigned) cookie - used for the session token itself,
     * which is already an opaque random value.
     */
    public static function queueRaw(string $suffix, string $value, int $ttlSeconds): void
    {
        Cookie::queue(self::make($suffix, $value, $ttlSeconds));
    }

    public static function forget(string $suffix): void
    {
        Cookie::queue(Cookie::forget(self::name($suffix)));
    }

    protected static function make(string $suffix, string $value, int $ttlSeconds): SymfonyCookie
    {
        return Cookie::make(
            name: self::name($suffix),
            value: $value,
            minutes: (int) ceil($ttlSeconds / 60),
            path: '/',
            domain: null,
            secure: app()->environment('production'),
            httpOnly: true,
            raw: false,
            sameSite: 'lax',
        );
    }

    protected static function key(): string
    {
        $key = config('auth_next.encryption_key');

        if (! $key || strlen($key) < 16) {
            throw new \RuntimeException('ENCRYPTION_KEY is missing or too short. Set it in .env (>= 32 bytes).');
        }

        return $key;
    }

    public static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64UrlDecode(string $data): string
    {
        $data = strtr($data, '-_', '+/');
        $pad = strlen($data) % 4;
        if ($pad) {
            $data .= str_repeat('=', 4 - $pad);
        }

        return base64_decode($data);
    }
}

<?php

namespace App\Helpers;

/**
 * Validates a user-supplied `?redirect=` target so a login link cannot bounce
 * the browser to another site. Only single-segment app paths pass.
 */
class SafeRedirect
{
    public static function path(?string $raw, string $fallback): string
    {
        if (! self::isSafe($raw)) {
            return $fallback;
        }

        // Admin sends people to its dashboard, not the bare prefix.
        return $raw === '/admin' ? '/admin/dashboard' : $raw;
    }

    private static function isSafe(?string $raw): bool
    {
        if ($raw === null || $raw === '' || ! self::isSafePath($raw)) {
            return false;
        }

        // Re-check after one decode pass to catch %2f / %5c style tricks.
        $decoded = rawurldecode($raw);

        return $decoded === $raw || self::isSafePath($decoded);
    }

    /**
     * Starts with exactly one "/" that is not followed by "/" or "\" (browsers
     * treat "\" as "/", so "/\evil.com" is protocol-relative just like "//evil.com").
     */
    private static function isSafePath(string $value): bool
    {
        return ! preg_match('/[\x00-\x1f]/', $value) && preg_match('#^/[^/\\\\]#', $value) === 1;
    }
}

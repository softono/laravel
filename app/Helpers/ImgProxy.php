<?php

namespace App\Helpers;

/**
 * Signed imgproxy URLs (https://docs.imgproxy.net/usage/signing_url), the same
 * scheme as Next's `imageCacheUrl`: HMAC-SHA256 over salt + path, base64url.
 */
class ImgProxy
{
    private const IMAGE_EXTENSIONS = '/\.(jpg|jpeg|png|gif|webp|bmp|avif)(\?.*)?$/i';

    public static function enabled(): bool
    {
        return (bool) config('files.imgproxy.enabled');
    }

    /** Whether `$url` points at something imgproxy can process. */
    public static function handles(string $url): bool
    {
        return preg_match(self::IMAGE_EXTENSIONS, $url) === 1;
    }

    /**
     * @param  string  $processing  imgproxy processing options, e.g. `rs:fill:64:64`
     */
    public static function url(string $sourceUrl, string $processing = ''): string
    {
        if (! self::enabled() || ! self::handles($sourceUrl)) {
            return $sourceUrl;
        }

        $key = self::hex((string) config('files.imgproxy.key'));
        $salt = self::hex((string) config('files.imgproxy.salt'));

        // Misconfigured (non-hex) key material: serve the original rather than a URL imgproxy will reject.
        if ($key === null || $salt === null) {
            return $sourceUrl;
        }

        $encoded = rtrim(strtr(base64_encode($sourceUrl), '+/', '-_'), '=');
        $path = $processing === '' ? '/'.$encoded : '/'.ltrim($processing, '/').'/'.$encoded;
        $signature = rtrim(strtr(base64_encode(hash_hmac('sha256', $salt.$path, $key, true)), '+/', '-_'), '=');

        return rtrim((string) config('files.imgproxy.url'), '/').'/'.$signature.$path;
    }

    private static function hex(string $value): ?string
    {
        return $value !== '' && strlen($value) % 2 === 0 && ctype_xdigit($value) ? hex2bin($value) : null;
    }
}

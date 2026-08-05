<?php

namespace App\Services\Storage\Signing;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * v1 request signing scheme - see Request Signing (v1) in docs/local/prd.md:
 *
 *   Authorization: HMAC {access_key}:{signature}
 *   Date: {RFC 1123 / ISO 8601 timestamp}
 *   Content-MD5: {base64 md5 of body, if present}
 *
 *   signature = base64(HMAC-SHA256(secret_key,
 *       method + "\n" + path + "\n" + date + "\n" + content-md5))
 *
 * The Date header doubles as a replay-window check.
 */
class HmacSigningStrategy implements SigningStrategy
{
    public function supports(Request $request): bool
    {
        return Str::startsWith((string) $request->header('Authorization'), 'HMAC ');
    }

    public function accessKey(Request $request): ?string
    {
        [$accessKey] = $this->parseAuthorizationHeader($request);

        return $accessKey;
    }

    public function verify(Request $request, string $secretKey): array
    {
        [$accessKey, $providedSignature] = $this->parseAuthorizationHeader($request);

        if (! $accessKey || ! $providedSignature) {
            return ['ok' => false, 'message' => 'Malformed Authorization header.'];
        }

        $date = $request->header('Date');
        if (! $date) {
            return ['ok' => false, 'message' => 'Missing Date header.'];
        }

        try {
            $requestTime = Carbon::parse($date);
        } catch (\Throwable) {
            return ['ok' => false, 'message' => 'Unparseable Date header.'];
        }

        $tolerance = (int) config('setting.storage_clock_skew_tolerance', 300);
        if (abs(now()->diffInSeconds($requestTime, false)) > $tolerance) {
            return ['ok' => false, 'message' => 'Request timestamp outside the allowed clock-skew window.'];
        }

        $contentMd5 = (string) $request->header('Content-MD5', '');
        $canonicalString = implode("\n", [
            $request->method(),
            '/'.ltrim($request->path(), '/'),
            $date,
            $contentMd5,
        ]);

        $expectedSignature = base64_encode(hash_hmac('sha256', $canonicalString, $secretKey, true));

        if (! hash_equals($expectedSignature, $providedSignature)) {
            return ['ok' => false, 'message' => 'Signature mismatch.'];
        }

        return ['ok' => true, 'message' => null];
    }

    /**
     * @return array{0: ?string, 1: ?string} [accessKey, signature]
     */
    protected function parseAuthorizationHeader(Request $request): array
    {
        $header = (string) $request->header('Authorization');
        if (! Str::startsWith($header, 'HMAC ')) {
            return [null, null];
        }

        $credential = trim(Str::after($header, 'HMAC '));
        if (! Str::contains($credential, ':')) {
            return [null, null];
        }

        [$accessKey, $signature] = explode(':', $credential, 2);

        return [$accessKey ?: null, $signature ?: null];
    }
}

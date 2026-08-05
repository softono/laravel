<?php

namespace App\Services\Storage\Signing;

use Illuminate\Http\Request;

/**
 * Pre-signed download URLs (v1) - GET only, works even against Private
 * buckets. See "Pre-signed URLs (v1)" in docs/local/prd.md:
 *
 *   GET /{bucket}/{object}?AccessKey={access_key}&Expires={unix_timestamp}&Signature={signature}
 *   signature = base64(HMAC-SHA256(secret_key, method + "\n" + path + "\n" + expires))
 */
class PresignedUrlStrategy implements SigningStrategy
{
    public function supports(Request $request): bool
    {
        return $request->query('AccessKey') !== null
            && $request->query('Expires') !== null
            && $request->query('Signature') !== null;
    }

    public function accessKey(Request $request): ?string
    {
        return $request->query('AccessKey');
    }

    public function verify(Request $request, string $secretKey): array
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return ['ok' => false, 'message' => 'Pre-signed URLs only support GET/HEAD.'];
        }

        $expires = (int) $request->query('Expires');
        if ($expires <= 0 || $expires < now()->timestamp) {
            return ['ok' => false, 'message' => 'Pre-signed URL has expired.'];
        }

        $providedSignature = (string) $request->query('Signature');

        $canonicalString = implode("\n", [
            $request->method(),
            '/'.ltrim($request->path(), '/'),
            (string) $expires,
        ]);

        $expectedSignature = base64_encode(hash_hmac('sha256', $canonicalString, $secretKey, true));

        if (! hash_equals($expectedSignature, $providedSignature)) {
            return ['ok' => false, 'message' => 'Signature mismatch.'];
        }

        return ['ok' => true, 'message' => null];
    }
}

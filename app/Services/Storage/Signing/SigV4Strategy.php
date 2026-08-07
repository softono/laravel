<?php

namespace App\Services\Storage\Signing;

use Illuminate\Http\Request;

/**
 * Real AWS Signature Version 4 support, so unmodified AWS SDKs / CLIs
 * (which always sign with SigV4, never the v1 HMAC scheme below) can talk
 * to this server. Supports both header-based auth
 * ("Authorization: AWS4-HMAC-SHA256 Credential=...") and query-string
 * "pre-signed URL" auth ("?X-Amz-Algorithm=AWS4-HMAC-SHA256&...").
 *
 * The payload hash is taken from the client-supplied x-amz-content-sha256
 * header/query param as-is (not recomputed from the body) - this avoids
 * consuming the php://input stream here, which the object-upload
 * controller still needs to read once, itself, for streaming to storage.
 */
class SigV4Strategy implements SigningStrategy
{
    protected const ALGORITHM = 'AWS4-HMAC-SHA256';

    public function supports(Request $request): bool
    {
        if (str_starts_with((string) $request->header('Authorization'), self::ALGORITHM.' ')) {
            return true;
        }

        return $request->query('X-Amz-Algorithm') === self::ALGORITHM
            && $request->query('X-Amz-Signature') !== null
            && $request->query('X-Amz-Credential') !== null;
    }

    public function accessKey(Request $request): ?string
    {
        $credential = $this->credentialScopeParts($request);

        return $credential[0] ?? null;
    }

    public function verify(Request $request, string $secretKey): array
    {
        $isPresigned = $request->query('X-Amz-Algorithm') === self::ALGORITHM;

        [$accessKey, $dateStamp, $region, $service] = $this->credentialScopeParts($request) + [null, null, null, null];
        if (! $accessKey || ! $dateStamp || ! $region || ! $service) {
            return ['ok' => false, 'message' => 'Malformed SigV4 credential scope.'];
        }

        $amzDate = $isPresigned
            ? (string) $request->query('X-Amz-Date')
            : (string) $request->header('x-amz-date');

        if (! $amzDate || ! preg_match('/^\d{8}T\d{6}Z$/', $amzDate)) {
            return ['ok' => false, 'message' => 'Missing or malformed x-amz-date.'];
        }

        try {
            $requestTime = \DateTime::createFromFormat('Ymd\THis\Z', $amzDate, new \DateTimeZone('UTC'));
        } catch (\Throwable) {
            $requestTime = false;
        }
        if (! $requestTime) {
            return ['ok' => false, 'message' => 'Unparseable x-amz-date.'];
        }

        if ($isPresigned) {
            $expires = (int) $request->query('X-Amz-Expires', 0);
            if ($expires <= 0 || (time() - $requestTime->getTimestamp()) > $expires) {
                return ['ok' => false, 'message' => 'Pre-signed URL has expired.'];
            }
        } else {
            $tolerance = (int) config('setting.storage_clock_skew_tolerance', 300);
            if (abs(time() - $requestTime->getTimestamp()) > $tolerance) {
                return ['ok' => false, 'message' => 'Request timestamp outside the allowed clock-skew window.'];
            }
        }

        $signedHeadersParam = $isPresigned
            ? (string) $request->query('X-Amz-SignedHeaders')
            : $this->authHeaderParam($request, 'SignedHeaders');

        if (! $signedHeadersParam) {
            return ['ok' => false, 'message' => 'Missing SignedHeaders.'];
        }
        $signedHeaders = explode(';', strtolower($signedHeadersParam));
        sort($signedHeaders);

        $providedSignature = $isPresigned
            ? (string) $request->query('X-Amz-Signature')
            : $this->authHeaderParam($request, 'Signature');

        if (! $providedSignature) {
            return ['ok' => false, 'message' => 'Missing signature.'];
        }

        $canonicalUri = $this->canonicalUri($request);
        $canonicalQueryString = $this->canonicalQueryString($request, $isPresigned);
        $canonicalHeaders = $this->canonicalHeaders($request, $signedHeaders);
        $payloadHash = $isPresigned
            ? 'UNSIGNED-PAYLOAD'
            : (string) $request->header('x-amz-content-sha256', 'UNSIGNED-PAYLOAD');

        $canonicalRequest = implode("\n", [
            $request->method(),
            $canonicalUri,
            $canonicalQueryString,
            $canonicalHeaders,
            implode(';', $signedHeaders),
            $payloadHash,
        ]);

        $credentialScope = "$dateStamp/$region/$service/aws4_request";
        $stringToSign = implode("\n", [
            self::ALGORITHM,
            $amzDate,
            $credentialScope,
            hash('sha256', $canonicalRequest),
        ]);

        $signingKey = $this->signingKey($secretKey, $dateStamp, $region, $service);
        $expectedSignature = hash_hmac('sha256', $stringToSign, $signingKey);

        if (! hash_equals($expectedSignature, $providedSignature)) {
            return ['ok' => false, 'message' => 'Signature mismatch.'];
        }

        return ['ok' => true, 'message' => null];
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: ?string, 3: ?string} [accessKey, dateStamp, region, service]
     */
    protected function credentialScopeParts(Request $request): array
    {
        $credential = $request->query('X-Amz-Algorithm') === self::ALGORITHM
            ? (string) $request->query('X-Amz-Credential')
            : (string) $this->authHeaderParam($request, 'Credential');

        if (! $credential || substr_count($credential, '/') < 4) {
            return [null, null, null, null];
        }

        [$accessKey, $dateStamp, $region, $service] = explode('/', $credential, 5);

        return [$accessKey ?: null, $dateStamp ?: null, $region ?: null, $service ?: null];
    }

    protected function authHeaderParam(Request $request, string $name): ?string
    {
        $header = (string) $request->header('Authorization');
        if (! preg_match('/'.preg_quote($name, '/').'=([^,]+)/', $header, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    protected function signingKey(string $secretKey, string $dateStamp, string $region, string $service): string
    {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4'.$secretKey, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);

        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }

    /**
     * The client signs the full request-target path it actually sent over
     * the wire - including the app's base path (e.g. /dev/storage/public
     * when not served from the domain root) - not Laravel's route-relative
     * path(), which has that base path already stripped.
     */
    protected function canonicalUri(Request $request): string
    {
        $path = parse_url($request->getRequestUri(), PHP_URL_PATH) ?: '/';
        $segments = array_map(
            fn ($segment) => rawurlencode(rawurldecode($segment)),
            explode('/', $path)
        );

        return implode('/', $segments) ?: '/';
    }

    protected function canonicalQueryString(Request $request, bool $isPresigned): string
    {
        $params = $request->query();
        unset($params['X-Amz-Signature']);

        $pairs = [];
        foreach ($params as $key => $value) {
            foreach ((array) $value as $v) {
                $pairs[] = $this->uriEncode((string) $key).'='.$this->uriEncode((string) $v);
            }
        }

        sort($pairs);

        return implode('&', $pairs);
    }

    protected function canonicalHeaders(Request $request, array $signedHeaders): string
    {
        $lines = [];
        foreach ($signedHeaders as $header) {
            $value = $header === 'host'
                ? (string) $request->header('host', $request->getHost())
                : (string) $request->header($header, '');

            $value = trim(preg_replace('/\s+/', ' ', $value));
            $lines[] = $header.':'.$value;
        }

        return implode("\n", $lines)."\n";
    }

    protected function uriEncode(string $value): string
    {
        return str_replace('%7E', '~', rawurlencode($value));
    }
}

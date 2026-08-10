<?php

namespace App\Http\Middleware;

use App\Helpers\Storage\S3Error;
use App\Repositories\Storage\ApiKeyRepository;
use App\Repositories\Storage\BucketRepository;
use App\Services\Storage\Signing\HmacSigningStrategy;
use App\Services\Storage\Signing\PresignedUrlStrategy;
use App\Services\Storage\Signing\SigV4Strategy;
use Closure;
use Illuminate\Support\Facades\Crypt;

/**
 * Authenticates S3-compatible API requests. Two independent, swappable
 * signing strategies are tried in turn (see SigningStrategy) - a future
 * AWS SigV4 strategy slots in the same way, without touching this class.
 *
 * Falls through to anonymous access only for GET/HEAD against a bucket
 * flagged Public (see Public Buckets in docs/local/prd.md) - everything
 * else requires a valid, active access key regardless of visibility.
 */
class StorageApiAuth
{
    public function __construct(
        protected ApiKeyRepository $apiKeys,
        protected BucketRepository $buckets,
        protected HmacSigningStrategy $hmacStrategy,
        protected PresignedUrlStrategy $presignedStrategy,
        protected SigV4Strategy $sigV4Strategy,
    ) {}

    public function handle($request, Closure $next)
    {
        // Debugbar injects an HTML/JS panel into every response body, which
        // corrupts the XML/binary/empty bodies real S3 clients (including
        // the AWS SDK) require on this route surface.
        if (app()->bound('debugbar')) {
            app('debugbar')->disable();
        }

        $strategies = [$this->hmacStrategy, $this->sigV4Strategy, $this->presignedStrategy];
        $strategy = null;

        foreach ($strategies as $candidate) {
            if ($candidate->supports($request)) {
                $strategy = $candidate;
                break;
            }
        }

        if ($strategy === null) {
            return $this->tryAnonymousPublicAccess($request, $next);
        }

        $accessKey = $strategy->accessKey($request);
        $apiKey = $accessKey ? $this->apiKeys->findByAccessKey($accessKey) : null;

        if (! $apiKey || ! $apiKey->isActive()) {
            return S3Error::send(401, 'InvalidAccessKeyId', 'Invalid or inactive access key.');
        }

        $secretKey = Crypt::decryptString($apiKey->secret_key);
        $result = $strategy->verify($request, $secretKey);

        if (! $result['ok']) {
            return S3Error::send(403, 'SignatureDoesNotMatch', $result['message'] ?? 'Authentication failed.');
        }

        $this->apiKeys->touchLastUsed($apiKey);

        $request->attributes->set('storage_api_key', $apiKey);
        $request->attributes->set('storage_user', $apiKey->user);

        return $next($request);
    }

    protected function tryAnonymousPublicAccess($request, Closure $next)
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return S3Error::send(401, 'AccessDenied', 'Authentication required.');
        }

        $bucketName = $request->route('bucket');
        $bucket = $bucketName ? $this->buckets->findByNameGlobal($bucketName) : null;

        if (! $bucket || ! $bucket->isPublic()) {
            return S3Error::send(401, 'AccessDenied', 'Authentication required.');
        }

        $request->attributes->set('storage_api_key', null);
        $request->attributes->set('storage_user', null);
        $request->attributes->set('storage_public_bucket', $bucket);

        return $next($request);
    }
}

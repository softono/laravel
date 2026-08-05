<?php

namespace App\Http\Middleware;

use App\Helpers\Response;
use App\Repositories\Storage\ApiUserRepository;
use App\Repositories\Storage\BucketRepository;
use App\Services\Storage\Signing\HmacSigningStrategy;
use App\Services\Storage\Signing\PresignedUrlStrategy;
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
        protected ApiUserRepository $apiUsers,
        protected BucketRepository $buckets,
        protected HmacSigningStrategy $hmacStrategy,
        protected PresignedUrlStrategy $presignedStrategy,
    ) {}

    public function handle($request, Closure $next)
    {
        $strategies = [$this->hmacStrategy, $this->presignedStrategy];
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
        $apiUser = $accessKey ? $this->apiUsers->findByAccessKey($accessKey) : null;

        if (! $apiUser || ! $apiUser->isActive()) {
            return Response::sendError(401, 'Invalid or inactive access key.');
        }

        $secretKey = Crypt::decryptString($apiUser->secret_key);
        $result = $strategy->verify($request, $secretKey);

        if (! $result['ok']) {
            return Response::sendError(401, $result['message'] ?? 'Authentication failed.');
        }

        $this->apiUsers->touchLastUsed($apiUser);

        $request->attributes->set('storage_api_user', $apiUser);
        $request->attributes->set('storage_user', $apiUser->user);

        return $next($request);
    }

    protected function tryAnonymousPublicAccess($request, Closure $next)
    {
        if (! in_array($request->method(), ['GET', 'HEAD'], true)) {
            return Response::sendError(401, 'Authentication required.');
        }

        $bucketName = $request->route('bucket');
        $bucket = $bucketName ? $this->buckets->findByNameGlobal($bucketName) : null;

        if (! $bucket || ! $bucket->isPublic()) {
            return Response::sendError(401, 'Authentication required.');
        }

        $request->attributes->set('storage_api_user', null);
        $request->attributes->set('storage_user', null);
        $request->attributes->set('storage_public_bucket', $bucket);

        return $next($request);
    }
}

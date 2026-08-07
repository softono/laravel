<?php

namespace App\Http\Controllers\Api\Storage;

use App\Helpers\Storage\S3Error;
use App\Models\Auth\User;
use App\Models\Storage\Bucket;
use App\Repositories\Storage\BucketRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

/**
 * Shared bucket-resolution/authorization logic for the S3-compatible API
 * controllers. Deliberately not app\Http\Controllers\Controller - this is
 * a stateless JSON/binary API, not a Blade-rendering panel controller.
 */
abstract class StorageApiController extends BaseController
{
    public function __construct(
        protected BucketRepository $buckets,
    ) {}

    /**
     * The authenticated Bucket Admin, or null for anonymous public-bucket
     * GET/HEAD - see StorageApiAuth.
     */
    protected function currentUser(Request $request): ?User
    {
        return $request->attributes->get('storage_user');
    }

    /**
     * Resolves {bucket} and enforces the tenancy/visibility rules from
     * docs/local/prd.md: writes always require ownership; anonymous reads
     * only work when the bucket is Public (StorageApiAuth already
     * refused non-GET/HEAD anonymous requests before this ever runs).
     *
     * @return array{0: ?Bucket, 1: ?JsonResponse} [bucket, earlyErrorResponse]
     */
    protected function resolveBucket(Request $request, string $bucketName, bool $requireOwnership = false): array
    {
        $bucket = $this->buckets->findByNameGlobal($bucketName);

        if (! $bucket) {
            return [null, S3Error::send(404, 'NoSuchBucket', 'The specified bucket does not exist.')];
        }

        $user = $this->currentUser($request);

        if ($user) {
            if ($bucket->user_id !== $user->id) {
                return [null, S3Error::send(403, 'AccessDenied', 'Access denied to this bucket.')];
            }

            return [$bucket, null];
        }

        // No authenticated user on this request - only reachable at all
        // for GET/HEAD on a Public bucket (StorageApiAuth gate).
        if ($requireOwnership || ! $bucket->isPublic()) {
            return [null, S3Error::send(401, 'AccessDenied', 'Authentication required.')];
        }

        return [$bucket, null];
    }
}

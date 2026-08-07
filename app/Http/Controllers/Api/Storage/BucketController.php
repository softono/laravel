<?php

namespace App\Http\Controllers\Api\Storage;

use App\Helpers\Storage\S3Error;
use App\Http\Resources\Storage\BucketResource;
use App\Repositories\Storage\BucketRepository;
use App\Services\Storage\BucketService;
use Illuminate\Http\Request;

/**
 * S3 Buckets subset - see "S3 Compatible APIs > Buckets" in docs/local/prd.md:
 *   GET    /          List buckets
 *   PUT    /{bucket}  Create bucket
 *   DELETE /{bucket}  Delete bucket
 * (GET /{bucket} - list objects - lives in ObjectController.)
 */
class BucketController extends StorageApiController
{
    public function __construct(
        BucketRepository $buckets,
        protected BucketService $bucketService,
    ) {
        parent::__construct($buckets);
    }

    public function index(Request $request)
    {
        $user = $this->currentUser($request);
        if (! $user) {
            return S3Error::send(401, 'AccessDenied', 'Authentication required.');
        }

        $buckets = $this->buckets->listForUser($user->id);

        return response()->json(['buckets' => BucketResource::collection($buckets)]);
    }

    public function store(Request $request, string $bucket)
    {
        $user = $this->currentUser($request);
        if (! $user) {
            return S3Error::send(401, 'AccessDenied', 'Authentication required.');
        }

        $result = $this->bucketService->create($user, [
            'name' => $bucket,
            'visibility' => $request->header('x-amz-acl') === 'public-read' ? 'public' : null,
        ]);

        if (! $result['status']) {
            return S3Error::send(400, 'InvalidRequest', $result['message']);
        }

        return response('', 200, [
            'Location' => '/'.$bucket,
        ]);
    }

    public function destroy(Request $request, string $bucket)
    {
        [$bucketModel, $error] = $this->resolveBucket($request, $bucket, requireOwnership: true);
        if ($error) {
            return $error;
        }

        $result = $this->bucketService->delete($bucketModel);

        if (! $result['status']) {
            return S3Error::send(409, 'BucketNotEmpty', $result['message']);
        }

        return response()->noContent();
    }
}

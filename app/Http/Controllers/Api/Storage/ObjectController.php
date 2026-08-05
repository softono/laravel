<?php

namespace App\Http\Controllers\Api\Storage;

use App\Helpers\Response as ApiResponse;
use App\Http\Resources\Storage\StorageObjectResource;
use App\Repositories\Storage\BucketRepository;
use App\Repositories\Storage\StorageObjectRepository;
use App\Services\Storage\ObjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * S3 Objects subset - see "S3 Compatible APIs > Objects" in docs/local/prd.md:
 *   GET    /{bucket}          List objects (list-type=2)
 *   PUT    /{bucket}/{object} Upload object (or Copy, via x-amz-copy-source)
 *   GET    /{bucket}/{object} Download object
 *   HEAD   /{bucket}/{object} Object metadata
 *   DELETE /{bucket}/{object} Delete object
 */
class ObjectController extends StorageApiController
{
    public function __construct(
        BucketRepository $buckets,
        protected ObjectService $objectService,
        protected StorageObjectRepository $objects,
    ) {
        parent::__construct($buckets);
    }

    /**
     * GET /{bucket}?list-type=2&prefix=&max-keys=&search=
     */
    public function index(Request $request, string $bucket)
    {
        [$bucketModel, $error] = $this->resolveBucket($request, $bucket);
        if ($error) {
            return $error;
        }

        $prefix = $request->query('prefix', '');
        $maxKeys = min((int) $request->query('max-keys', 1000), 1000) ?: 1000;
        $offset = (int) $request->query('offset', 0);
        $search = $request->query('search');

        $result = $this->objectService->list($bucketModel, $prefix ?: null, $maxKeys, $offset, $search);

        return response()->json([
            'name' => $bucketModel->name,
            'prefix' => $prefix,
            'key_count' => $result['data']->count(),
            'max_keys' => $maxKeys,
            'is_truncated' => ($offset + $result['data']->count()) < $result['total'],
            'contents' => StorageObjectResource::collection($result['data']),
        ]);
    }

    /**
     * PUT /{bucket}/{object}
     * A x-amz-copy-source header switches this into an S3-standard Copy
     * (see Object Management > Move/Copy Object in docs/local/prd.md);
     * otherwise the request body is streamed to disk as a new upload.
     */
    public function store(Request $request, string $bucket, string $object)
    {
        [$bucketModel, $error] = $this->resolveBucket($request, $bucket, requireOwnership: true);
        if ($error) {
            return $error;
        }

        $copySource = $request->header('x-amz-copy-source');
        if ($copySource) {
            return $this->copy($request, $bucketModel, $object, $copySource);
        }

        $maxUploadSize = (int) config('setting.storage_max_upload_size');
        $contentLength = (int) $request->header('Content-Length', 0);
        if ($maxUploadSize > 0 && $contentLength > $maxUploadSize) {
            return ApiResponse::sendError(413, 'Upload exceeds the maximum allowed size.');
        }

        $allowedTypes = trim((string) config('setting.storage_allowed_file_types', '*'));
        if ($allowedTypes !== '' && $allowedTypes !== '*') {
            $extension = strtolower(pathinfo($object, PATHINFO_EXTENSION));
            $allowed = array_map('trim', explode(',', strtolower($allowedTypes)));
            if ($extension === '' || ! in_array($extension, $allowed, true)) {
                return ApiResponse::sendError(415, 'File type not allowed.');
            }
        }

        $input = fopen('php://input', 'rb');
        if ($input === false) {
            return ApiResponse::sendError(500, 'Unable to read request body.');
        }

        try {
            $storageObject = $this->objectService->storeStream(
                $bucketModel,
                $object,
                $input,
                basename($object),
                $request->header('Content-Type'),
                $this->extractMetadata($request)
            );
        } finally {
            if (is_resource($input)) {
                fclose($input);
            }
        }

        Log::info('storage.object.uploaded', ['bucket' => $bucket, 'key' => $object, 'size' => $storageObject->size]);

        return response()->json(['message' => 'Object uploaded successfully.'], 200, [
            'ETag' => $storageObject->etag(),
        ]);
    }

    protected function copy(Request $request, $destBucketModel, string $destObjectKey, string $copySource)
    {
        // x-amz-copy-source is "/{source-bucket}/{source-key}" (URL-encoded key).
        $decoded = rawurldecode(ltrim($copySource, '/'));
        [$sourceBucketName, $sourceObjectKey] = array_pad(explode('/', $decoded, 2), 2, null);

        if (! $sourceBucketName || ! $sourceObjectKey) {
            return ApiResponse::sendError(400, 'Malformed x-amz-copy-source header.');
        }

        [$sourceBucketModel, $error] = $this->resolveBucket($request, $sourceBucketName, requireOwnership: true);
        if ($error) {
            return $error;
        }

        $sourceObject = $this->objectService->find($sourceBucketModel, $sourceObjectKey);
        if (! $sourceObject) {
            return ApiResponse::sendError(404, 'Source object does not exist.');
        }

        $copied = $this->objectService->copy($sourceBucketModel, $sourceObject, $destBucketModel, $destObjectKey);

        return response()->json(['message' => 'Object copied successfully.'], 200, [
            'ETag' => $copied->etag(),
        ]);
    }

    /**
     * GET /{bucket}/{object}
     */
    public function show(Request $request, string $bucket, string $object)
    {
        [$bucketModel, $error] = $this->resolveBucket($request, $bucket);
        if ($error) {
            return $error;
        }

        $storageObject = $this->objectService->find($bucketModel, $object);
        if (! $storageObject) {
            return ApiResponse::sendError(404, 'The specified key does not exist.');
        }

        return $this->objectService->download($bucketModel, $storageObject, $this->metadataHeaders($storageObject));
    }

    /**
     * HEAD /{bucket}/{object}
     */
    public function head(Request $request, string $bucket, string $object)
    {
        [$bucketModel, $error] = $this->resolveBucket($request, $bucket);
        if ($error) {
            return $error;
        }

        $storageObject = $this->objectService->find($bucketModel, $object);
        if (! $storageObject) {
            return response('', 404);
        }

        return response('', 200, array_merge([
            'Content-Type' => $storageObject->mime_type ?: 'application/octet-stream',
            'Content-Length' => (string) $storageObject->size,
            'ETag' => $storageObject->etag(),
            'Last-Modified' => $storageObject->updated_at?->toRfc7231String(),
        ], $this->metadataHeaders($storageObject)));
    }

    /**
     * DELETE /{bucket}/{object}
     */
    public function destroy(Request $request, string $bucket, string $object)
    {
        [$bucketModel, $error] = $this->resolveBucket($request, $bucket, requireOwnership: true);
        if ($error) {
            return $error;
        }

        $storageObject = $this->objectService->find($bucketModel, $object);
        if (! $storageObject) {
            return response()->noContent(); // S3 DELETE is idempotent.
        }

        $this->objectService->delete($bucketModel, $storageObject);

        return response()->noContent();
    }

    /**
     * Captures arbitrary client-supplied x-amz-meta-* request headers as
     * JSON key/value pairs - see Object Management > Metadata in
     * docs/local/prd.md. No server-side content inspection.
     */
    protected function extractMetadata(Request $request): array
    {
        $metadata = [];
        foreach ($request->headers->all() as $name => $values) {
            if (str_starts_with($name, 'x-amz-meta-')) {
                $metadata[substr($name, strlen('x-amz-meta-'))] = $values[0] ?? '';
            }
        }

        return $metadata;
    }

    /**
     * Echoes stored x-amz-meta-* metadata back on GET/HEAD.
     */
    protected function metadataHeaders($storageObject): array
    {
        $headers = [];
        foreach ((array) $storageObject->metadata_json as $key => $value) {
            $headers['x-amz-meta-'.$key] = $value;
        }

        return $headers;
    }
}

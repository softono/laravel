<?php

namespace App\Services\Storage;

use App\Models\Storage\Bucket;
use App\Models\Storage\StorageObject;
use App\Repositories\Storage\StorageObjectRepository;
use Illuminate\Support\Collection;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Business logic for object upload/download/delete/copy/list. Controllers
 * stay thin (see Architecture in docs/local/prd.md) - all filesystem and
 * checksum work happens here.
 */
class ObjectService
{
    public function __construct(
        protected StorageObjectRepository $objects,
        protected BucketPathResolver $paths,
    ) {}

    /**
     * Streams the request body straight to disk in fixed-size chunks -
     * never buffers the full upload in memory - while computing the MD5
     * checksum incrementally. Returns the saved StorageObject.
     *
     * @param  resource  $inputStream  An open, readable stream (e.g. fopen('php://input', 'rb')).
     * @param  array<string, string>  $metadata  Arbitrary x-amz-meta-* key/values.
     */
    public function storeStream(
        Bucket $bucket,
        string $objectKey,
        $inputStream,
        string $originalFilename,
        ?string $mimeType,
        array $metadata = []
    ): StorageObject {
        $relativeStoragePath = $this->paths->newRelativeStoragePath($originalFilename);
        $fullRelativePath = $this->paths->fullRelativePath($bucket, $relativeStoragePath);
        $disk = $this->paths->disk();

        $disk->makeDirectory(dirname($fullRelativePath));
        $absolutePath = $disk->path($fullRelativePath);

        $destination = fopen($absolutePath, 'wb');
        if ($destination === false) {
            throw new RuntimeException('Unable to open destination file for writing.');
        }

        $hashContext = hash_init('md5');
        $size = 0;

        try {
            while (! feof($inputStream)) {
                $chunk = fread($inputStream, 1024 * 1024);
                if ($chunk === false) {
                    break;
                }
                hash_update($hashContext, $chunk);
                fwrite($destination, $chunk);
                $size += strlen($chunk);
            }
        } finally {
            fclose($destination);
        }

        $checksum = hash_final($hashContext);

        $existing = $this->objects->findByKey($bucket->id, $objectKey);
        if ($existing) {
            // S3 PUT semantics: overwriting an existing key replaces it -
            // clean up the old physical file once the new one lands safely.
            $oldFullPath = $this->paths->fullRelativePath($bucket, $existing->relative_storage_path);
            $this->objects->update($existing, [
                'original_filename' => $originalFilename,
                'mime_type' => $mimeType,
                'size' => $size,
                'checksum' => $checksum,
                'relative_storage_path' => $relativeStoragePath,
                'metadata_json' => $metadata,
            ]);
            if ($oldFullPath !== $fullRelativePath && $disk->exists($oldFullPath)) {
                $disk->delete($oldFullPath);
            }

            return $existing->fresh();
        }

        return $this->objects->create([
            'bucket_id' => $bucket->id,
            'object_key' => $objectKey,
            'original_filename' => $originalFilename,
            'mime_type' => $mimeType,
            'size' => $size,
            'checksum' => $checksum,
            'relative_storage_path' => $relativeStoragePath,
            'metadata_json' => $metadata,
        ]);
    }

    public function find(Bucket $bucket, string $objectKey): ?StorageObject
    {
        return $this->objects->findByKey($bucket->id, $objectKey);
    }

    /**
     * Streams the object body directly to the HTTP response - never loads
     * the full file into memory (see API Requirements: "Stream downloads").
     */
    public function download(Bucket $bucket, StorageObject $object, array $extraHeaders = []): StreamedResponse
    {
        $disk = $this->paths->disk();
        $fullRelativePath = $this->paths->fullRelativePath($bucket, $object->relative_storage_path);

        return new StreamedResponse(function () use ($disk, $fullRelativePath) {
            $stream = $disk->readStream($fullRelativePath);
            if ($stream === null) {
                return;
            }
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, array_merge([
            'Content-Type' => $object->mime_type ?: 'application/octet-stream',
            'Content-Length' => (string) $object->size,
            'ETag' => $object->etag(),
            'Last-Modified' => $object->updated_at?->toRfc7231String(),
            'Content-Disposition' => 'attachment; filename="'.addslashes($object->original_filename).'"',
        ], $extraHeaders));
    }

    public function delete(Bucket $bucket, StorageObject $object): void
    {
        $disk = $this->paths->disk();
        $fullRelativePath = $this->paths->fullRelativePath($bucket, $object->relative_storage_path);

        if ($disk->exists($fullRelativePath)) {
            $disk->delete($fullRelativePath);
        }

        $this->objects->delete($object);
    }

    /**
     * S3-standard copy (PUT + x-amz-copy-source). Move is Copy+Delete,
     * implemented by the caller - see Move/Copy mechanics in
     * docs/local/prd.md Open Questions.
     */
    public function copy(Bucket $sourceBucket, StorageObject $source, Bucket $destBucket, string $destObjectKey): StorageObject
    {
        $disk = $this->paths->disk();
        $sourceFullPath = $this->paths->fullRelativePath($sourceBucket, $source->relative_storage_path);

        $relativeStoragePath = $this->paths->newRelativeStoragePath($source->original_filename);
        $destFullPath = $this->paths->fullRelativePath($destBucket, $relativeStoragePath);

        $disk->makeDirectory(dirname($destFullPath));
        $disk->copy($sourceFullPath, $destFullPath);

        $existing = $this->objects->findByKey($destBucket->id, $destObjectKey);
        if ($existing) {
            $oldFullPath = $this->paths->fullRelativePath($destBucket, $existing->relative_storage_path);
            $this->objects->update($existing, [
                'original_filename' => $source->original_filename,
                'mime_type' => $source->mime_type,
                'size' => $source->size,
                'checksum' => $source->checksum,
                'relative_storage_path' => $relativeStoragePath,
                'metadata_json' => $source->metadata_json,
            ]);
            if ($disk->exists($oldFullPath)) {
                $disk->delete($oldFullPath);
            }

            return $existing->fresh();
        }

        return $this->objects->create([
            'bucket_id' => $destBucket->id,
            'object_key' => $destObjectKey,
            'original_filename' => $source->original_filename,
            'mime_type' => $source->mime_type,
            'size' => $source->size,
            'checksum' => $source->checksum,
            'relative_storage_path' => $relativeStoragePath,
            'metadata_json' => $source->metadata_json,
        ]);
    }

    /**
     * ListObjectsV2-style pagination (list-type=2 query param).
     *
     * @return array{data: Collection, total: int}
     */
    public function list(Bucket $bucket, ?string $prefix, int $limit, int $offset, ?string $search = null): array
    {
        return [
            'data' => $this->objects->listByPrefix($bucket->id, $prefix, $limit, $offset, $search),
            'total' => $this->objects->countByPrefix($bucket->id, $prefix, $search),
        ];
    }

    /**
     * Groups object keys under $prefix into immediate "folders" (virtual -
     * derived from '/' splits, never persisted) and direct-child objects,
     * matching S3's CommonPrefixes/Contents split for a delimited listing.
     */
    public function listFolder(Bucket $bucket, string $prefix): array
    {
        $keys = $this->objects->listImmediateKeys($bucket->id, $prefix);
        $folders = [];
        $files = [];

        foreach ($keys as $key) {
            $remainder = substr($key, strlen($prefix));
            $slashPos = strpos($remainder, '/');

            if ($slashPos === false) {
                $files[] = $key;
            } else {
                $folders[$prefix.substr($remainder, 0, $slashPos + 1)] = true;
            }
        }

        return ['folders' => array_keys($folders), 'files' => $files];
    }
}

<?php

namespace App\Services\Storage;

use App\Models\Auth\User;
use App\Models\Storage\Bucket;
use App\Repositories\Storage\BucketRepository;
use App\Repositories\Storage\StorageObjectRepository;
use Illuminate\Support\Facades\Validator;

/**
 * Bucket names follow the same charset restrictions as real S3 (lowercase
 * letters, digits, dots and hyphens) so client tooling built against S3
 * behaves the same way here.
 */
class BucketService
{
    public function __construct(
        protected BucketRepository $buckets,
        protected StorageObjectRepository $objects,
        protected BucketPathResolver $paths,
    ) {}

    /**
     * @return array{status: int, message: string, bucket?: Bucket}
     */
    public function create(User $user, array $postData): array
    {
        $validator = Validator::make($postData, [
            'name' => ['required', 'string', 'min:3', 'max:63', 'regex:/^[a-z0-9]([a-z0-9.-]*[a-z0-9])?$/'],
            'visibility' => 'nullable|in:public,private',
            'storage_quota' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }

        if ($this->buckets->findByNameGlobal($postData['name'])) {
            return ['status' => 0, 'message' => 'A bucket with this name already exists.'];
        }

        $bucket = $this->buckets->create([
            'name' => $postData['name'],
            'visibility' => $postData['visibility'] ?? config('setting.storage_default_visibility', 'private'),
            'user_id' => $user->id,
            'storage_quota' => $postData['storage_quota'] ?? null,
        ]);

        $this->paths->disk()->makeDirectory($this->paths->bucketDirectory($bucket));

        return ['status' => 1, 'message' => 'Bucket created successfully.', 'bucket' => $bucket];
    }

    public function update(Bucket $bucket, array $postData): array
    {
        $validator = Validator::make($postData, [
            'visibility' => 'required|in:public,private',
            'storage_quota' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }

        $bucket->update([
            'visibility' => $postData['visibility'],
            'storage_quota' => $postData['storage_quota'] ?? null,
        ]);

        return ['status' => 1, 'message' => 'Bucket updated successfully.'];
    }

    /**
     * @return array{status: int, message: string}
     */
    public function delete(Bucket $bucket): array
    {
        if ($this->objects->countForBucket($bucket->id) > 0) {
            return ['status' => 0, 'message' => 'Bucket is not empty. Delete all objects first.'];
        }

        $this->paths->disk()->deleteDirectory($this->paths->bucketDirectory($bucket));
        $this->buckets->delete($bucket);

        return ['status' => 1, 'message' => 'Bucket deleted successfully.'];
    }

    public function stats(Bucket $bucket): array
    {
        return [
            'object_count' => $this->objects->countForBucket($bucket->id),
            'storage_used' => $this->objects->sizeForBucket($bucket->id),
        ];
    }
}

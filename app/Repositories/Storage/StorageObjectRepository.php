<?php

namespace App\Repositories\Storage;

use App\Models\Storage\Bucket;
use App\Models\Storage\StorageObject;
use Illuminate\Support\Facades\DB;

class StorageObjectRepository
{
    public function findByKey(int $bucketId, string $objectKey): ?StorageObject
    {
        return StorageObject::where('bucket_id', $bucketId)->where('object_key', $objectKey)->first();
    }

    public function create(array $data): StorageObject
    {
        return StorageObject::create($data);
    }

    public function update(StorageObject $object, array $data): bool
    {
        return $object->update($data);
    }

    public function delete(StorageObject $object): ?bool
    {
        return $object->delete();
    }

    /**
     * S3 ListObjectsV2-style listing: optional key prefix, capped page
     * size, lexicographic key order. Pagination is a simple offset/limit
     * here (no continuation-token bookkeeping) - see API Requirements.
     */
    public function listByPrefix(int $bucketId, ?string $prefix, int $limit, int $offset, ?string $search = null)
    {
        $query = StorageObject::where('bucket_id', $bucketId);

        if ($prefix) {
            $query->where('object_key', 'like', $prefix.'%');
        }
        if ($search) {
            $query->where('object_key', 'like', '%'.$search.'%');
        }

        return $query->orderBy('object_key')->offset($offset)->limit($limit)->get();
    }

    public function countByPrefix(int $bucketId, ?string $prefix, ?string $search = null): int
    {
        $query = StorageObject::where('bucket_id', $bucketId);

        if ($prefix) {
            $query->where('object_key', 'like', $prefix.'%');
        }
        if ($search) {
            $query->where('object_key', 'like', '%'.$search.'%');
        }

        return $query->count();
    }

    public function countForBucket(int $bucketId): int
    {
        return StorageObject::where('bucket_id', $bucketId)->count();
    }

    public function sizeForBucket(int $bucketId): int
    {
        return (int) StorageObject::where('bucket_id', $bucketId)->sum('size');
    }

    public function countForUser(string $userId): int
    {
        return StorageObject::whereHas('bucket', fn ($q) => $q->where('user_id', $userId))->count();
    }

    public function sizeForUser(string $userId): int
    {
        return (int) StorageObject::whereHas('bucket', fn ($q) => $q->where('user_id', $userId))->sum('size');
    }

    public function countAll(): int
    {
        return StorageObject::count();
    }

    public function sizeAll(): int
    {
        return (int) StorageObject::sum('size');
    }

    public function recentUploads(int $limit = 10, ?string $userId = null)
    {
        $query = StorageObject::with('bucket')->orderByDesc('created_at')->limit($limit);

        if ($userId) {
            $query->whereHas('bucket', fn ($q) => $q->where('user_id', $userId));
        }

        return $query->get();
    }

    public function existsAnyInBucket(Bucket $bucket): bool
    {
        return StorageObject::where('bucket_id', $bucket->id)->exists();
    }

    /** Distinct top-level prefixes ("folders") directly under $prefix within a bucket. */
    public function listImmediateKeys(int $bucketId, string $prefix): array
    {
        return DB::table('storage_objects')
            ->where('bucket_id', $bucketId)
            ->where('object_key', 'like', $prefix.'%')
            ->pluck('object_key')
            ->all();
    }
}

<?php

namespace App\Services\Storage;

use App\Constants\UserRole;
use App\Models\Auth\User;
use App\Repositories\Auth\UserRepository;
use App\Repositories\Storage\ApiKeyRepository;
use App\Repositories\Storage\BucketRepository;
use App\Repositories\Storage\StorageObjectRepository;

/**
 * Backs both dashboards - system-wide (Super Admin) and personal (Bucket
 * Admin) - see docs/local/prd.md.
 */
class StorageStatsService
{
    public function __construct(
        protected BucketRepository $buckets,
        protected StorageObjectRepository $objects,
        protected ApiKeyRepository $apiKeys,
        protected UserRepository $users,
    ) {}

    public function systemWide(): array
    {
        return [
            'total_storage_used' => $this->objects->sizeAll(),
            'total_buckets' => $this->buckets->countAll(),
            'total_objects' => $this->objects->countAll(),
            'total_users' => $this->users->countByRoles([UserRole::USER]),
            'total_api_keys' => $this->apiKeys->countAll(),
            'recent_uploads' => $this->objects->recentUploads(10),
        ];
    }

    public function perUserBreakdown()
    {
        return $this->buckets->storageBreakdownByUser();
    }

    public function personal(User $user): array
    {
        return [
            'storage_used' => $this->objects->sizeForUser($user->id),
            'bucket_count' => $this->buckets->countForUser($user->id),
            'object_count' => $this->objects->countForUser($user->id),
            'recent_uploads' => $this->objects->recentUploads(10, $user->id),
        ];
    }
}

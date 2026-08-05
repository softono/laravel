<?php

namespace App\Repositories\Storage;

use App\Models\Storage\ApiUser;

class ApiUserRepository
{
    public function findById(int $id): ?ApiUser
    {
        return ApiUser::find($id);
    }

    public function findByAccessKey(string $accessKey): ?ApiUser
    {
        return ApiUser::where('access_key', $accessKey)->first();
    }

    public function listForUser(string $userId)
    {
        return ApiUser::where('user_id', $userId)->orderByDesc('created_at')->get();
    }

    public function create(array $data): ApiUser
    {
        return ApiUser::create($data);
    }

    public function update(ApiUser $apiUser, array $data): bool
    {
        return $apiUser->update($data);
    }

    public function delete(ApiUser $apiUser): ?bool
    {
        return $apiUser->delete();
    }

    public function touchLastUsed(ApiUser $apiUser): void
    {
        $apiUser->timestamps = false;
        $apiUser->update(['last_used_at' => now()]);
    }

    public function countForUser(string $userId): int
    {
        return ApiUser::where('user_id', $userId)->count();
    }

    public function countAll(): int
    {
        return ApiUser::count();
    }
}

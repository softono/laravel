<?php

namespace App\Repositories\Storage;

use App\Models\Storage\ApiKey;

class ApiKeyRepository
{
    public function findById(int $id): ?ApiKey
    {
        return ApiKey::find($id);
    }

    public function findByAccessKey(string $accessKey): ?ApiKey
    {
        return ApiKey::where('access_key', $accessKey)->first();
    }

    public function listForUser(string $userId)
    {
        return ApiKey::where('user_id', $userId)->orderByDesc('created_at')->get();
    }

    public function create(array $data): ApiKey
    {
        return ApiKey::create($data);
    }

    public function update(ApiKey $apiKey, array $data): bool
    {
        return $apiKey->update($data);
    }

    public function delete(ApiKey $apiKey): ?bool
    {
        return $apiKey->delete();
    }

    public function touchLastUsed(ApiKey $apiKey): void
    {
        $apiKey->timestamps = false;
        $apiKey->update(['last_used_at' => now()]);
    }

    public function countForUser(string $userId): int
    {
        return ApiKey::where('user_id', $userId)->count();
    }

    public function countAll(): int
    {
        return ApiKey:count();
    }
}

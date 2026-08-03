<?php

namespace App\Repositories\Auth;

use App\Models\Auth\UserVerification;

class UserVerificationRepository
{
    public function findByKey(string $key): ?UserVerification
    {
        return UserVerification::where('key', $key)->first();
    }

    public function createOrUpdate(string $key, array $attributes): UserVerification
    {
        return UserVerification::updateOrCreate(
            ['key' => $key],
            $attributes
        );
    }

    public function deleteByKey(string $key): int
    {
        return UserVerification::where('key', $key)->delete();
    }
}

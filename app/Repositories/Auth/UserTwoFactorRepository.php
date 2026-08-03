<?php

namespace App\Repositories\Auth;

use App\Models\Auth\UserTwoFactor;

class UserTwoFactorRepository
{
    public function findByUserId(string $userId): ?UserTwoFactor
    {
        return UserTwoFactor::where('user_id', $userId)->first();
    }

    public function create(array $data): UserTwoFactor
    {
        return UserTwoFactor::create($data);
    }

    public function update(UserTwoFactor $twoFactor, array $data): bool
    {
        return $twoFactor->update($data);
    }

    public function delete(UserTwoFactor $twoFactor): ?bool
    {
        return $twoFactor->delete();
    }
}

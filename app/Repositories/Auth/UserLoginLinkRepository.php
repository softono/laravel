<?php

namespace App\Repositories\Auth;

use App\Models\Auth\UserLoginLink;

class UserLoginLinkRepository
{
    public function findById(string $id): ?UserLoginLink
    {
        return UserLoginLink::find($id);
    }

    public function findByTokenHash(string $tokenHash): ?UserLoginLink
    {
        return UserLoginLink::where('token_hash', $tokenHash)->first();
    }

    public function create(array $data): UserLoginLink
    {
        return UserLoginLink::create($data);
    }

    public function claimApproved(string $id): int
    {
        return UserLoginLink::where('id', $id)
            ->where('status', 'approved')
            ->update(['status' => 'consumed']);
    }
}

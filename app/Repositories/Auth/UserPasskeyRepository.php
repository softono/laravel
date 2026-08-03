<?php

namespace App\Repositories\Auth;

use App\Models\Auth\UserPasskey;
use Illuminate\Database\Eloquent\Collection;

class UserPasskeyRepository
{
    public function findByCredentialId(string $credentialId): ?UserPasskey
    {
        return UserPasskey::where('credential_id', $credentialId)->first();
    }

    public function getByUserId(string $userId): Collection
    {
        return UserPasskey::where('user_id', $userId)->get();
    }

    public function create(array $data): UserPasskey
    {
        return UserPasskey::create($data);
    }

    public function delete(UserPasskey $passkey): ?bool
    {
        return $passkey->delete();
    }
}

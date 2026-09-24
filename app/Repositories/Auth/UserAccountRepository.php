<?php

namespace App\Repositories\Auth;

use App\Models\Auth\UserAccount;

class UserAccountRepository
{
    public function findCredentialAccount(string $userId): ?UserAccount
    {
        return UserAccount::where('user_id', $userId)
            ->where('provider_id', 'credential')
            ->first();
    }

    /** An OAuth link: the provider's own id for the person is stored in `account_id`. */
    public function findProviderAccount(string $providerId, string $providerAccountId): ?UserAccount
    {
        return UserAccount::where('provider_id', $providerId)
            ->where('account_id', $providerAccountId)
            ->first();
    }

    /** @return string[] provider ids linked to the user (`credential` = a password is set) */
    public function providersFor(string $userId): array
    {
        return UserAccount::where('user_id', $userId)->pluck('provider_id')->all();
    }

    public function create(array $data): UserAccount
    {
        return UserAccount::create($data);
    }

    /** Creates the `credential` account on first use, otherwise replaces its password hash. */
    public function saveCredentialPassword(string $userId, string $hashedPassword): UserAccount
    {
        return UserAccount::updateOrCreate(
            ['user_id' => $userId, 'provider_id' => 'credential'],
            ['account_id' => $userId, 'password' => $hashedPassword],
        );
    }
}

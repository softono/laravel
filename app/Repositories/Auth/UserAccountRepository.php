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

    public function findProviderAccount(string $providerId, string $providerSubject): ?UserAccount
    {
        return UserAccount::where('provider_id', $providerId)
            ->where('provider_subject', $providerSubject)
            ->first();
    }

    public function create(array $data): UserAccount
    {
        return UserAccount::create($data);
    }

    public function updateCredentialPassword(string $userId, string $hashedPassword): bool
    {
        $account = $this->findCredentialAccount($userId);
        if (! $account) {
            return false;
        }

        return $account->update(['credential' => $hashedPassword]);
    }
}

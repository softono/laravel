<?php

namespace App\Repositories\Auth;

use App\Models\Auth\UserSession;
use DateTimeInterface;

class UserSessionRepository
{
    public function findByToken(string $token): ?UserSession
    {
        return UserSession::where('token', $token)->first();
    }

    public function create(array $data): UserSession
    {
        return UserSession::create($data);
    }

    public function touch(UserSession $session, DateTimeInterface $expiresAt): bool
    {
        return $session->update([
            'last_active_at' => now(),
            'expires_at' => $expiresAt,
        ]);
    }

    public function revoke(UserSession $session): ?bool
    {
        return $session->delete();
    }

    public function revokeAllForUser(string $userId): int
    {
        return UserSession::where('user_id', $userId)->delete();
    }
}

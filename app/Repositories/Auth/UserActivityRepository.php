<?php

namespace App\Repositories\Auth;

use App\Models\Auth\UserActivity;

class UserActivityRepository
{
    public function create(array $data): UserActivity
    {
        return UserActivity::create($data);
    }

    public function getByUserId(string $userId, int $limit = 50)
    {
        return UserActivity::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}

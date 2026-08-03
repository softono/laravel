<?php

namespace App\Services\Auth;

use App\Helpers\ClientInfo;
use App\Models\Auth\UserActivity;
use App\Repositories\Auth\UserActivityRepository;
use Illuminate\Http\Request;

/**
 * User-activity logging, used across the auth, account, 2FA, and
 * login-link flows. `type` is always one of the App\Constants\UserActivity
 * keys.
 */
class ActivityService
{
    public function __construct(
        protected UserActivityRepository $activities,
    ) {}

    public function log(Request $request, string $userId, string $type, ?array $data = null): UserActivity
    {
        return $this->activities->create([
            'user_id' => $userId,
            'device_id' => ClientInfo::deviceUid($request),
            'type' => $type,
            'data' => $data ? json_encode($data) : null,
            'ip' => ClientInfo::ip($request),
            'client' => ClientInfo::userAgent($request),
            'location' => null,
        ]);
    }
}

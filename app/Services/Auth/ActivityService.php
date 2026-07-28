<?php

namespace App\Services\Auth;

use App\Helpers\ClientInfo;
use App\Models\Auth\UserActivity;
use Illuminate\Http\Request;

/**
 * Port of the Next app's user-activity logging (used across auth.service.ts,
 * account.service.ts, tfa/*, login-link.service.ts, etc). `type` is always
 * one of the App\Constants\UserActivity keys.
 */
class ActivityService
{
    public function log(Request $request, string $userId, string $type, ?array $data = null): UserActivity
    {
        return UserActivity::create([
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

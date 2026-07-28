<?php

namespace App\Services\Auth;

use App\Models\Auth\UserDevice;
use App\Helpers\ClientInfo;
use Illuminate\Http\Request;

/**
 * Port of the Next app's src/server/modules/auth/device.service.ts -
 * trusted devices, keyed on the long-lived device_uid cookie. A trusted
 * device short-circuits the 2FA prompt at login for trust_days days.
 */
class DeviceService
{
    public function isTrusted(Request $request, string $userId): bool
    {
        $deviceUid = ClientInfo::deviceUid($request);

        if (! $deviceUid) {
            return false;
        }

        return UserDevice::where('user_id', $userId)
            ->where('device_uid', $deviceUid)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function trust(Request $request, string $userId): ?UserDevice
    {
        $deviceUid = ClientInfo::deviceUid($request);

        if (! $deviceUid) {
            return null;
        }

        $expiresAt = now()->addDays((int) config('auth_next.trust_days'));

        $device = UserDevice::where('user_id', $userId)->where('device_uid', $deviceUid)->first();

        if ($device) {
            $device->update([
                'ip_address' => ClientInfo::ip($request),
                'user_agent' => ClientInfo::userAgent($request),
                'trusted_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            return $device;
        }

        return UserDevice::create([
            'user_id' => $userId,
            'device_uid' => $deviceUid,
            'ip_address' => ClientInfo::ip($request),
            'user_agent' => ClientInfo::userAgent($request),
            'trusted_at' => now(),
            'expires_at' => $expiresAt,
        ]);
    }

    public function revokeAll(string $userId): void
    {
        UserDevice::where('user_id', $userId)->delete();
    }
}

<?php

namespace App\Services\Auth;

use App\Helpers\ClientInfo;
use App\Models\Auth\UserDevice;
use App\Repositories\Auth\UserDeviceRepository;
use Illuminate\Http\Request;

/**
 * Trusted devices, keyed on the long-lived device_uid cookie. A trusted
 * device short-circuits the 2FA prompt at login for trust_days days.
 */
class DeviceService
{
    public function __construct(
        protected UserDeviceRepository $userDevices,
    ) {}

    public function isTrusted(Request $request, string $userId): bool
    {
        $deviceUid = ClientInfo::deviceUid($request);

        if (! $deviceUid) {
            return false;
        }

        $device = $this->userDevices->findByDeviceUid($userId, $deviceUid);

        return $device && $device->expires_at && $device->expires_at->isFuture();
    }

    public function trust(Request $request, string $userId): ?UserDevice
    {
        $deviceUid = ClientInfo::deviceUid($request);

        if (! $deviceUid) {
            return null;
        }

        $expiresAt = now()->addDays((int) config('auth_next.trust_days'));

        $device = $this->userDevices->findByDeviceUid($userId, $deviceUid);

        if ($device) {
            $device->update([
                'ip_address' => ClientInfo::ip($request),
                'user_agent' => ClientInfo::userAgent($request),
                'trusted_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            return $device;
        }

        return $this->userDevices->create([
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

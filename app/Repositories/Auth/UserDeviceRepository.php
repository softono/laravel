<?php

namespace App\Repositories\Auth;

use App\Models\Auth\UserDevice;
use Illuminate\Database\Eloquent\Collection;

class UserDeviceRepository
{
    public function findByDeviceUid(string $userId, string $deviceUid): ?UserDevice
    {
        return UserDevice::where('user_id', $userId)
            ->where('device_uid', $deviceUid)
            ->first();
    }

    public function create(array $data): UserDevice
    {
        return UserDevice::create($data);
    }

    public function getTrustedDevices(string $userId): Collection
    {
        return UserDevice::where('user_id', $userId)
            ->where('is_trusted', true)
            ->get();
    }

    public function delete(UserDevice $device): ?bool
    {
        return $device->delete();
    }
}

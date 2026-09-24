<?php

namespace App\Modules\User\Services;

use App\Constants\UserActivity;
use App\Constants\UserStatus;
use App\Helpers\General;
use App\Models\Auth\User;
use App\Modules\Auth\Services\SessionService;
use App\Repositories\Auth\UserAccountRepository;
use App\Repositories\Auth\UserRepository;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * Self-service profile changes. Privileged fields (role, status, permission,
 * two_factor_enabled) are never touched here; the admin modules do that.
 */
class ProfileService
{
    protected const PROFILE_FIELDS = ['first_name', 'last_name', 'phone', 'country', 'timezone'];

    public function __construct(
        protected UserRepository $users,
        protected ActivityService $activity,
        protected SessionService $sessions,
        protected UserAccountRepository $userAccounts,
        protected General $general,
    ) {}

    /** False for accounts created through Google that never chose a password. */
    public function hasPassword(User $user): bool
    {
        return (bool) $this->userAccounts->findCredentialAccount($user->id)?->password;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{ok: bool, message: string}
     */
    public function update(Request $request, User $user, array $data): array
    {
        $changes = array_intersect_key($data, array_flip(self::PROFILE_FIELDS));

        if (! empty($changes['phone']) && $this->phoneTaken($changes['phone'], $user->id)) {
            return ['ok' => false, 'message' => 'Phone number already in use'];
        }

        $old = $user->only(array_keys($changes));

        $this->users->update($user, $changes);
        $this->sessions->invalidateUserCache($user->id);
        $this->activity->log($request, $user->id, UserActivity::ACCOUNT_UPDATE, ['old' => $old, 'new' => $changes]);

        return ['ok' => true, 'message' => 'Profile updated successfully'];
    }

    /**
     * @return array{ok: bool, message: string, image?: string}
     */
    public function saveImage(Request $request, User $user, UploadedFile $file): array
    {
        $upload = $this->general->uploadFile($file, 'profile');

        if (! $upload['status']) {
            return ['ok' => false, 'message' => $upload['message']];
        }

        $this->removeStoredImage($user);
        $this->users->update($user, ['image' => $upload['file_name']]);
        $this->sessions->invalidateUserCache($user->id);
        $this->activity->log($request, $user->id, UserActivity::IMAGE_UPLOADED);

        return ['ok' => true, 'message' => 'Profile image updated successfully', 'image' => $upload['file_name']];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function deleteImage(User $user): array
    {
        $this->removeStoredImage($user);
        $this->users->update($user, ['image' => null]);
        $this->sessions->invalidateUserCache($user->id);

        return ['ok' => true, 'message' => 'Profile image deleted successfully'];
    }

    /** Marks the account inactive and signs it out everywhere. */
    public function deactivate(Request $request, User $user): void
    {
        $this->users->update($user, ['status' => UserStatus::INACTIVE]);
        $this->activity->log($request, $user->id, UserActivity::ACCOUNT_DEACTIVATE);
        $this->sessions->revokeAllForUser($user->id);
        $this->sessions->invalidateUserCache($user->id);
    }

    protected function phoneTaken(string $phone, string $exceptUserId): bool
    {
        $existing = $this->users->findByPhone($phone);

        return $existing !== null && $existing->id !== $exceptUserId;
    }

    protected function removeStoredImage(User $user): void
    {
        if ($user->image) {
            $this->general->deleteFile($user->image, 'profile');
        }
    }
}

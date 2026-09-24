<?php

namespace App\Modules\Admin\Services;

use App\Constants\UserActivity;
use App\Constants\UserRole;
use App\Constants\UserStatus;
use App\Helpers\ApiResult;
use App\Helpers\General;
use App\Models\Auth\User;
use App\Modules\Auth\Services\SessionService;
use App\Repositories\Auth\UserAccountRepository;
use App\Repositories\Auth\UserRepository;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Create, update, activate and delete accounts from the admin panel. Every
 * call is scoped to one role (USER for Admin/User, ADMIN for Admin/Admins), so
 * neither screen can touch accounts of the other kind.
 */
class AccountManagementService
{
    protected const FIELDS = ['first_name', 'last_name', 'phone', 'country', 'timezone', 'status', 'permission', 'two_factor_enabled'];

    public function __construct(
        protected UserRepository $users,
        protected UserAccountRepository $userAccounts,
        protected SessionService $sessions,
        protected ActivityService $activity,
        protected General $general,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Request $request, User $actor, string $role, array $data): array
    {
        $email = strtolower(trim($data['email']));

        if ($conflict = $this->uniqueConflict($email, $data['phone'] ?? null)) {
            return ApiResult::failure($conflict);
        }

        $user = $this->users->create([
            'email' => $email,
            'role' => $role,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'country' => $data['country'] ?? null,
            'timezone' => $data['timezone'] ?? 'UTC',
            'status' => $data['status'] ?? UserStatus::ACTIVE,
            'permission' => $this->permissionString($data),
            'image' => $this->storeImage($data),
        ]);

        $this->userAccounts->create([
            'user_id' => $user->id,
            'account_id' => $user->id,
            'provider_id' => 'credential',
            'password' => Hash::make($data['password']),
        ]);

        $this->activity->log($request, $actor->id, $this->activityType($role), ['action' => 'created', 'user_id' => $user->id]);

        return ApiResult::success('Saved successfully');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Request $request, User $actor, string $role, string $id, array $data): array
    {
        $user = $this->users->findByIdAndRole($id, $role);

        if (! $user) {
            return ApiResult::failure('No data found');
        }

        $email = strtolower(trim($data['email']));
        $emailChanged = $email !== $user->email;
        $phoneChanged = ! empty($data['phone']) && $data['phone'] !== $user->phone;

        if ($conflict = $this->uniqueConflict($emailChanged ? $email : null, $phoneChanged ? $data['phone'] : null)) {
            return ApiResult::failure($conflict);
        }

        $changes = array_intersect_key($data, array_flip(self::FIELDS));
        if (array_key_exists('permission', $data)) {
            $changes['permission'] = $this->permissionString($data);
        }
        if ($emailChanged) {
            $changes['email'] = $email;
            $changes['email_verified'] = false;
        }
        if ($image = $this->storeImage($data)) {
            $this->removeImage($user);
            $changes['image'] = $image;
        }

        $this->users->update($user, $changes);

        if (! empty($data['password'])) {
            $this->userAccounts->saveCredentialPassword($user->id, Hash::make($data['password']));
            $this->sessions->revokeAllForUser($user->id);
        }

        $this->sessions->invalidateUserCache($user->id);
        $this->activity->log($request, $actor->id, $this->activityType($role), ['action' => 'updated', 'user_id' => $user->id]);

        return ApiResult::success('Saved successfully');
    }

    public function toggleStatus(Request $request, User $actor, string $role, string $id): array
    {
        $user = $this->users->findByIdAndRole($id, $role);

        if (! $user) {
            return ApiResult::failure('No data found');
        }

        if ($user->id === $actor->id) {
            return ApiResult::failure('You cannot change your own status');
        }

        $status = $user->isActive() ? UserStatus::INACTIVE : UserStatus::ACTIVE;
        $this->users->update($user, ['status' => $status]);

        if ($status === UserStatus::INACTIVE) {
            $this->sessions->revokeAllForUser($user->id);
        }

        $this->sessions->invalidateUserCache($user->id);
        $this->activity->log($request, $actor->id, $this->activityType($role), ['action' => $status, 'user_id' => $user->id]);

        return ApiResult::success('Status updated successfully');
    }

    public function delete(Request $request, User $actor, string $role, string $id): array
    {
        $user = $this->users->findByIdAndRole($id, $role);

        if (! $user) {
            return ApiResult::failure('No data found');
        }

        if ($user->id === $actor->id) {
            return ApiResult::failure('You cannot delete your own account');
        }

        $this->removeImage($user);
        $this->sessions->revokeAllForUser($user->id);
        $this->sessions->invalidateUserCache($user->id);
        $this->users->delete($user);
        $this->activity->log($request, $actor->id, $this->activityType($role), ['action' => 'deleted', 'user_id' => $id]);

        return ApiResult::success('Data deleted successfully');
    }

    protected function uniqueConflict(?string $email, ?string $phone): ?string
    {
        if ($email && $this->users->findByEmail($email)) {
            return 'Email already in use';
        }

        if ($phone && $this->users->findByPhone($phone)) {
            return 'Phone number already in use';
        }

        return null;
    }

    /** Permission checkboxes arrive as an array; the column stores a comma-joined string. */
    protected function permissionString(array $data): string
    {
        return implode(',', $data['permission'] ?? []);
    }

    protected function storeImage(array $data): ?string
    {
        if (empty($data['image'])) {
            return null;
        }

        $upload = $this->general->uploadFile($data['image'], 'profile');

        return $upload['status'] ? $upload['data']['file_name'] : null;
    }

    protected function removeImage(User $user): void
    {
        if ($user->image) {
            $this->general->deleteFile($user->image, 'profile');
        }
    }

    protected function activityType(string $role): string
    {
        return $role === UserRole::USER ? UserActivity::USER_UPDATE : UserActivity::ADMIN_UPDATE;
    }
}

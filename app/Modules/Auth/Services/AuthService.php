<?php

namespace App\Modules\Auth\Services;

use App\Constants\UserActivity;
use App\Models\Auth\User;
use App\Repositories\Auth\UserAccountRepository;
use App\Repositories\Auth\UserRepository;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Handles the password-credential login/admin-login path, logout, and
 * password change/set. Session issuance, 2FA gating and OTP-based flows
 * are wired in from the controllers.
 */
class AuthService
{
    /**
     * A fixed argon2id hash with no known plaintext. Checked against on
     * every "unknown email" / "not authorized" path so failing that check
     * costs the same wall-clock time as a real password comparison -
     * login cannot be used to enumerate which emails exist or which
     * accounts are admins.
     */
    protected const DUMMY_HASH = '$argon2id$v=19$m=65536,t=3,p=4$NS9vSjh3akVRNzJ3NjRtaQ$mtLdlnOBlHWDHywGBSMhzDDzq708V1H+d9npwQQnUxA';

    public function __construct(
        protected SessionService $sessions,
        protected ActivityService $activity,
        protected UserRepository $users,
        protected UserAccountRepository $userAccounts,
    ) {}

    public function dummyPasswordCheck(): void
    {
        Hash::check('dummy', self::DUMMY_HASH);
    }

    /**
     * @return array{ok: bool, message: ?string, user: ?User, requiresTfa: bool}
     */
    public function authenticate(Request $request, string $email, string $password, bool $requireAdmin = false): array
    {
        $email = strtolower(trim($email));
        $user = $this->users->findByEmail($email);
        // dd($user);
        if (! $user || ($requireAdmin && ! $user->isAdmin())) {
            $this->dummyPasswordCheck();

            return ['ok' => false, 'message' => 'Invalid email or password', 'user' => null, 'requiresTfa' => false];
        }

        if (! $user->isActive()) {
            return ['ok' => false, 'message' => 'Account is disabled', 'user' => null, 'requiresTfa' => false];
        }

        $account = $this->userAccounts->findCredentialAccount($user->id);

        if (! $account || ! $account->password) {
            $this->dummyPasswordCheck();

            return ['ok' => false, 'message' => 'Invalid email or password', 'user' => null, 'requiresTfa' => false];
        }

        if (! Hash::check($password, $account->password)) {
            $this->activity->log($request, $user->id, UserActivity::LOGIN_FAILED);

            return ['ok' => false, 'message' => 'Invalid email or password', 'user' => null, 'requiresTfa' => false];
        }

        // Transparent bcrypt -> argon2id upgrade for anyone migrated from the legacy app.
        if (Hash::needsRehash($account->password)) {
            $account->update(['password' => Hash::make($password)]);
        }

        return ['ok' => true, 'message' => null, 'user' => $user, 'requiresTfa' => (bool) $user->two_factor_enabled];
    }

    public function logSuccess(Request $request, User $user, string $type = UserActivity::LOGIN_SUCCESS): void
    {
        $this->activity->log($request, $user->id, $type);
    }

    public function logout(?string $token, ?string $userId, Request $request): void
    {
        if ($token) {
            $this->sessions->revoke($token);
        }

        if ($userId) {
            $this->activity->log($request, $userId, UserActivity::LOGOUT);
        }
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function changePassword(Request $request, User $user, string $currentPassword, string $newPassword): array
    {
        $account = $this->userAccounts->findCredentialAccount($user->id);

        if (! $account || ! $account->password || ! Hash::check($currentPassword, $account->password)) {
            return ['ok' => false, 'message' => 'Current password is incorrect'];
        }

        $account->update(['password' => Hash::make($newPassword)]);

        // Deliberate Next behaviour: revoking ALL sessions logs the user out
        // of the browser they're currently using too.
        $this->sessions->revokeAllForUser($user->id);
        $this->activity->log($request, $user->id, UserActivity::PASSWORD_CHANGED);

        return ['ok' => true, 'message' => 'Password changed successfully'];
    }

    public function setPassword(Request $request, User $user, string $newPassword): array
    {
        $account = $this->userAccounts->findCredentialAccount($user->id);

        if ($account) {
            $account->update(['password' => Hash::make($newPassword)]);
        } else {
            $this->userAccounts->create([
                'user_id' => $user->id,
                'account_id' => $user->id,
                'provider_id' => 'credential',
                'password' => Hash::make($newPassword),
            ]);
        }

        $this->activity->log($request, $user->id, UserActivity::PASSWORD_SET);

        return ['ok' => true, 'message' => 'Password set successfully'];
    }
}

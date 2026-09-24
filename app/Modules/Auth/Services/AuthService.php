<?php

namespace App\Modules\Auth\Services;

use App\Constants\UserActivity;
use App\Helpers\ApiResult;
use App\Helpers\General;
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
        protected LoginAttemptService $attempts,
        protected General $general,
    ) {}

    public function dummyPasswordCheck(): void
    {
        Hash::check('dummy', self::DUMMY_HASH);
    }

    /**
     * `data` carries the authenticated `user` model and `requires_tfa` for the controller; it is never sent as is.
     *
     * @return array{http_status: int, status: int, message: string, data: array{user?: User, requires_tfa?: bool, requires_captcha?: bool}}
     */
    public function authenticate(Request $request, string $email, string $password, bool $requireAdmin = false): array
    {
        $email = strtolower(trim($email));

        if ($this->attempts->isLocked($email)) {
            return ApiResult::failure('Too many failed login attempts. Please try again in a few minutes.');
        }

        if ($this->attempts->needsCaptcha($email) && $this->general->recaptchaFails()) {
            return ApiResult::failure('Please complete the captcha verification', ['requires_captcha' => true]);
        }

        $user = $this->users->findByEmail($email);

        if (! $user || ($requireAdmin && ! $user->isAdmin())) {
            $this->dummyPasswordCheck();
            $this->attempts->recordFailure($email);

            return ApiResult::failure('Invalid email or password');
        }

        $account = $this->userAccounts->findCredentialAccount($user->id);

        if (! $account || ! $account->password) {
            $this->dummyPasswordCheck();
            $this->attempts->recordFailure($email);

            return ApiResult::failure('Invalid email or password');
        }

        if (! Hash::check($password, $account->password)) {
            $this->attempts->recordFailure($email);
            $this->activity->log($request, $user->id, UserActivity::LOGIN_FAILED);

            return ApiResult::failure('Invalid email or password');
        }

        // Only reveal a disabled account to someone who proved the password, so the message
        // cannot be used to discover which emails are registered.
        if (! $user->isActive()) {
            return ApiResult::failure('Account is disabled');
        }

        $this->attempts->clear($email);

        // Transparent bcrypt -> argon2id upgrade for anyone migrated from the legacy app.
        if (Hash::needsRehash($account->password)) {
            $account->update(['password' => Hash::make($password)]);
        }

        return ApiResult::success('', ['user' => $user, 'requires_tfa' => (bool) $user->two_factor_enabled]);
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

    public function changePassword(Request $request, User $user, string $currentPassword, string $newPassword): array
    {
        $account = $this->userAccounts->findCredentialAccount($user->id);

        if (! $account || ! $account->password || ! Hash::check($currentPassword, $account->password)) {
            return ApiResult::failure('Current password is incorrect', [], 422);
        }

        $account->update(['password' => Hash::make($newPassword)]);

        // Deliberate Next behaviour: revoking ALL sessions logs the user out
        // of the browser they're currently using too.
        $this->sessions->revokeAllForUser($user->id);
        $this->activity->log($request, $user->id, UserActivity::PASSWORD_CHANGED);

        return ApiResult::success('Password changed successfully');
    }

    /**
     * For accounts that have no password yet (Google-only sign-ups). Replacing an
     * existing one has to go through changePassword(), which checks the current
     * password - otherwise a hijacked session could take the account over.
     */
    public function setPassword(Request $request, User $user, string $newPassword): array
    {
        $account = $this->userAccounts->findCredentialAccount($user->id);

        if ($account?->password) {
            return ApiResult::failure('A password is already set. Use change password instead.');
        }

        $this->userAccounts->saveCredentialPassword($user->id, Hash::make($newPassword));
        $this->sessions->invalidateUserCache($user->id);
        $this->activity->log($request, $user->id, UserActivity::PASSWORD_SET);

        return ApiResult::success('Password set successfully');
    }
}

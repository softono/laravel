<?php

namespace App\Modules\Auth\Services;

use App\Constants\UserActivity;
use App\Helpers\ApiResult;
use App\Helpers\SignedCookie;
use App\Models\Auth\User;
use App\Models\Auth\UserTwoFactor;
use App\Modules\Auth\Services\Tfa\BackupCodeMethod;
use App\Modules\Auth\Services\Tfa\EmailOtpMethod;
use App\Modules\Auth\Services\Tfa\TotpMethod;
use App\Repositories\Auth\UserAccountRepository;
use App\Repositories\Auth\UserRepository;
use App\Repositories\Auth\UserTwoFactorRepository;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Registry of 2FA verification methods (authenticator app / email OTP /
 * backup code) plus the challenge lifecycle shared across them, and the
 * account-side enable/disable/regenerate-codes flows.
 */
class TfaService
{
    public function __construct(
        protected TotpMethod $totp,
        protected EmailOtpMethod $emailOtp,
        protected BackupCodeMethod $backupCodes,
        protected ChallengeService $challenges,
        protected DeviceService $devices,
        protected SessionService $sessions,
        protected AuthService $auth,
        protected ActivityService $activity,
        protected UserRepository $users,
        protected UserAccountRepository $userAccounts,
        protected UserTwoFactorRepository $userTwoFactors,
    ) {}

    /**
     * Starts a 2FA challenge for a login that has already passed the
     * password check. Sets the signed {uid}_tfa cookie and returns
     * `data.requires_tfa = true`.
     */
    public function startLoginChallenge(Request $request, User $user, bool $remember): array
    {
        $handle = $this->challenges->createTfa($user->id, $remember);
        SignedCookie::queue('tfa', $handle, (int) config('auth_next.tfa_ttl'));

        return ApiResult::success('', ['requires_tfa' => true]);
    }

    /** `data.methods`: the challenge methods available to the current handle's user. */
    public function getChallengeMethods(Request $request): array
    {
        $handle = $request->cookie(SignedCookie::name('tfa'));
        $signed = $handle ? SignedCookie::verify($handle) : null;
        $data = $signed ? $this->challenges->peekTfa($signed) : null;

        if (! $data) {
            return ApiResult::success('', ['methods' => []]);
        }

        $methods = ['otp', 'link'];
        $tf = $this->userTwoFactors->findByUserId($data['user_id']);

        if ($tf && $tf->verified) {
            $methods[] = 'totp';
        }

        if ($tf && $tf->backup_codes && json_decode($tf->backup_codes, true)) {
            $methods[] = 'backup';
        }

        return ApiResult::success('', ['methods' => $methods]);
    }

    /**
     * Verifies a 2FA login challenge and, on success, issues the session.
     */
    public function verifyLoginChallenge(Request $request, string $method, string $code, bool $trustDevice): array
    {
        $cookieValue = $request->cookie(SignedCookie::name('tfa'));
        $handle = $cookieValue ? SignedCookie::verify($cookieValue) : null;

        if (! $handle) {
            return ApiResult::failure('Verification session expired. Please log in again.', [], 422);
        }

        if ($this->challenges->tfaAttemptsExceeded($handle)) {
            $this->challenges->consumeTfa($handle);

            return ApiResult::failure('Too many failed attempts. Please log in again.', [], 409);
        }

        $pending = $this->challenges->peekTfa($handle);

        if (! $pending) {
            return ApiResult::failure('Verification session expired. Please log in again.', [], 401);
        }

        $user = $this->users->findById($pending['user_id']);

        if (! $user) {
            $this->challenges->consumeTfa($handle);

            return ApiResult::failure('Verification session expired. Please log in again.', [], 401);
        }

        $result = $this->verifyByMethod($user, $method, $code);

        if (! $result['status']) {
            $this->challenges->bumpTfaAttempts($handle);

            return $result;
        }

        $this->challenges->consumeTfa($handle);

        if ($trustDevice) {
            $this->devices->trust($request, $user->id);
        }

        $session = $this->sessions->issue($request, $user->id, $pending['remember']);
        $this->auth->logSuccess($request, $user, UserActivity::LOGIN_SUCCESS);

        $ttlSeconds = $pending['remember']
            ? config('auth_next.session_ttl_days.remember') * 86400
            : config('auth_next.session_ttl_days.default') * 86400;

        SignedCookie::queueRaw('session_token', $session->token, $ttlSeconds);
        SignedCookie::forget('tfa');

        return ApiResult::success('Logged in successfully');
    }

    /** Checks a TOTP, email-OTP or backup code for the user (a used backup code is consumed). */
    public function verifyByMethod(User $user, string $method, string $code): array
    {
        return match ($method) {
            'totp' => $this->verifyTotp($user, $code),
            'otp' => $this->emailOtp->verify($user->email, $code),
            'backup' => $this->verifyBackup($user, $code),
            default => ApiResult::failure('Unknown verification method', [], 422),
        };
    }

    protected function verifyTotp(User $user, string $code): array
    {
        $tf = $this->userTwoFactors->findByUserId($user->id);

        if (! $tf || ! $tf->verified) {
            return ApiResult::failure('Authenticator app is not set up', [], 422);
        }

        return $this->totp->verify($tf->secret, $code) ? ApiResult::success() : ApiResult::failure('Invalid code', [], 422);
    }

    protected function verifyBackup(User $user, string $code): array
    {
        $tf = $this->userTwoFactors->findByUserId($user->id);

        if (! $tf) {
            return ApiResult::failure('No backup codes available', [], 422);
        }

        $result = $this->backupCodes->verify($tf->backup_codes, $code);

        if ($result['status']) {
            $tf->update(['backup_codes' => $result['data']['remaining']]);
        }

        return $result;
    }

    // --- Account-side setup/management -------------------------------------------------

    public function sendLoginChallengeOtp(Request $request): array
    {
        $handle = $request->cookie(SignedCookie::name('tfa'));
        $signed = $handle ? SignedCookie::verify($handle) : null;
        $data = $signed ? $this->challenges->peekTfa($signed) : null;

        $user = $data ? $this->users->findById($data['user_id']) : null;

        if ($user) {
            $this->emailOtp->send($user);
        }

        return ApiResult::success('A verification code has been sent to your email');
    }

    public function getStatus(User $user): array
    {
        $tf = $this->userTwoFactors->findByUserId($user->id);

        return ApiResult::success('', [
            'enabled' => (bool) $user->two_factor_enabled,
            'totp_verified' => $tf ? (bool) $tf->verified : false,
            'backup_codes_remaining' => $tf ? count(json_decode($tf->backup_codes, true) ?: []) : 0,
        ]);
    }

    public function enable(Request $request, User $user, string $password): array
    {
        $account = $this->userAccounts->findCredentialAccount($user->id);

        if (! $account || ! $account->password || ! Hash::check($password, $account->password)) {
            return ApiResult::failure('Current password is incorrect', [], 422);
        }

        $secret = $this->totp->generateSecret();
        $codes = $this->backupCodes->generate();

        UserTwoFactor::updateOrCreate(
            ['user_id' => $user->id],
            [
                'secret' => $this->totp->encryptSecret($secret),
                'backup_codes' => json_encode($this->backupCodes->hash($codes)),
                'verified' => false,
            ],
        );

        $issuer = config('setting.app_name') ?: config('app.name');
        $uri = $this->totp->otpAuthUri($secret, $user->email, $issuer);

        return ApiResult::success('', [
            'secret' => $secret,
            'totp_uri' => $uri,
            'qr_svg' => $this->totp->qrCodeSvg($uri),
            'backup_codes' => $codes,
        ]);
    }

    public function verifySetup(Request $request, User $user, string $method, string $code): array
    {
        $tf = $this->userTwoFactors->findByUserId($user->id);

        if (! $tf) {
            return ApiResult::failure('Two-factor setup not started', [], 422);
        }

        $valid = match ($method) {
            'totp' => $this->totp->verify($tf->secret, $code),
            'otp' => (bool) $this->emailOtp->verify($user->email, $code)['status'],
            default => false,
        };

        if (! $valid) {
            return ApiResult::failure('Invalid code', [], 422);
        }

        $tf->update(['verified' => true]);
        $user->update(['two_factor_enabled' => true]);
        $this->sessions->invalidateUserCache($user->id);
        $this->activity->log($request, $user->id, UserActivity::TFA_ENABLED);

        return ApiResult::success('Two-factor authentication enabled');
    }

    public function disable(Request $request, User $user, string $password): array
    {
        $account = $this->userAccounts->findCredentialAccount($user->id);

        if (! $account || ! $account->password || ! Hash::check($password, $account->password)) {
            return ApiResult::failure('Current password is incorrect', [], 422);
        }

        $tf = $this->userTwoFactors->findByUserId($user->id);
        if ($tf) {
            $this->userTwoFactors->delete($tf);
        }
        $user->update(['two_factor_enabled' => false]);
        $this->sessions->invalidateUserCache($user->id);
        $this->devices->revokeAll($user->id);
        $this->activity->log($request, $user->id, UserActivity::TFA_DISABLED);

        return ApiResult::success('Two-factor authentication disabled');
    }

    /** Removes only the authenticator app, keeping email-OTP/backup-code 2FA available. */
    public function removeAuthenticator(Request $request, User $user): array
    {
        $tf = $this->userTwoFactors->findByUserId($user->id);

        if (! $tf) {
            return ApiResult::failure('Authenticator app is not set up', [], 422);
        }

        $tf->update(['verified' => false]);
        $this->activity->log($request, $user->id, UserActivity::TFA_AUTHENTICATOR_REMOVED);

        return ApiResult::success('Authenticator app removed');
    }

    public function regenerateBackupCodes(Request $request, User $user): array
    {
        $tf = $this->userTwoFactors->findByUserId($user->id);

        if (! $tf) {
            return ApiResult::failure('Two-factor authentication is not enabled', [], 422);
        }

        $codes = $this->backupCodes->generate();
        $tf->update(['backup_codes' => json_encode($this->backupCodes->hash($codes))]);
        $this->activity->log($request, $user->id, UserActivity::BACKUP_CODES_REGENERATED);

        return ApiResult::success('Backup codes regenerated', ['codes' => $codes]);
    }
}

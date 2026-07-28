<?php

namespace App\Services\Auth;

use App\Constants\UserActivity;
use App\Models\Auth\User;
use App\Models\Auth\UserAccount;
use App\Models\Auth\UserTwoFactor;
use App\Services\Auth\Tfa\BackupCodeMethod;
use App\Services\Auth\Tfa\EmailOtpMethod;
use App\Services\Auth\Tfa\TotpMethod;
use App\Helpers\ApiResult;
use App\Helpers\SignedCookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Port of the Next app's src/server/modules/auth/tfa/*.ts - registry of
 * verification methods (authenticator app / email OTP / backup code)
 * plus the challenge lifecycle shared across them, and the account-side
 * enable/disable/regenerate-codes flows.
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
    ) {}

    /**
     * Starts a 2FA challenge for a login that has already passed the
     * password check. Sets the signed {uid}_tfa cookie and returns the
     * {next:'tfa'} envelope response.
     */
    public function startLoginChallenge(Request $request, User $user, bool $remember)
    {
        $handle = $this->challenges->createTfa($user->id, $remember);
        SignedCookie::queue('tfa', $handle, (int) config('auth_next.tfa_ttl'));

        return ApiResult::success(null, ['next' => 'tfa'])->toResponse();
    }

    /** @return string[] available challenge methods for the current handle's user */
    public function getChallengeMethods(Request $request): array
    {
        $handle = $request->cookie(SignedCookie::name('tfa'));
        $signed = $handle ? SignedCookie::verify($handle) : null;
        $data = $signed ? $this->challenges->peekTfa($signed) : null;

        if (! $data) {
            return [];
        }

        $methods = ['otp'];
        $tf = UserTwoFactor::where('user_id', $data['user_id'])->first();

        if ($tf && $tf->verified) {
            $methods[] = 'totp';
        }

        if ($tf && $tf->backup_codes && json_decode($tf->backup_codes, true)) {
            $methods[] = 'backup';
        }

        return $methods;
    }

    /**
     * Verifies a 2FA login challenge and, on success, issues the session.
     */
    public function verifyLoginChallenge(Request $request, string $method, string $code, bool $trustDevice)
    {
        $cookieValue = $request->cookie(SignedCookie::name('tfa'));
        $handle = $cookieValue ? SignedCookie::verify($cookieValue) : null;

        if (! $handle) {
            return ApiResult::failure('Verification session expired. Please log in again.')->toResponse();
        }

        if ($this->challenges->tfaAttemptsExceeded($handle)) {
            $this->challenges->consumeTfa($handle);

            return ApiResult::failure('Too many failed attempts. Please log in again.')->toResponse();
        }

        $pending = $this->challenges->peekTfa($handle);

        if (! $pending) {
            return ApiResult::failure('Verification session expired. Please log in again.')->toResponse();
        }

        $user = User::find($pending['user_id']);

        if (! $user) {
            $this->challenges->consumeTfa($handle);

            return ApiResult::failure('Verification session expired. Please log in again.')->toResponse();
        }

        $result = $this->verifyByMethod($user, $method, $code);

        if (! $result['valid']) {
            $this->challenges->bumpTfaAttempts($handle);

            return ApiResult::failure($result['message'] ?? 'Invalid code')->toResponse();
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

        return ApiResult::success('Logged in successfully', ['next' => 'dashboard'])->toResponse();
    }

    /** @return array{valid: bool, message: ?string} */
    protected function verifyByMethod(User $user, string $method, string $code): array
    {
        return match ($method) {
            'totp' => $this->verifyTotp($user, $code),
            'otp' => $this->emailOtp->verify($user->email, $code),
            'backup' => $this->verifyBackup($user, $code),
            default => ['valid' => false, 'message' => 'Unknown verification method'],
        };
    }

    protected function verifyTotp(User $user, string $code): array
    {
        $tf = UserTwoFactor::where('user_id', $user->id)->first();

        if (! $tf || ! $tf->verified) {
            return ['valid' => false, 'message' => 'Authenticator app is not set up'];
        }

        return ['valid' => $this->totp->verify($tf->secret, $code), 'message' => 'Invalid code'];
    }

    protected function verifyBackup(User $user, string $code): array
    {
        $tf = UserTwoFactor::where('user_id', $user->id)->first();

        if (! $tf) {
            return ['valid' => false, 'message' => 'No backup codes available'];
        }

        $result = $this->backupCodes->verify($tf->backup_codes, $code);

        if ($result['valid']) {
            $tf->update(['backup_codes' => $result['remaining']]);
        }

        return ['valid' => $result['valid'], 'message' => 'Invalid backup code'];
    }

    // --- Account-side setup/management -------------------------------------------------

    public function sendLoginChallengeOtp(Request $request): void
    {
        $handle = $request->cookie(SignedCookie::name('tfa'));
        $signed = $handle ? SignedCookie::verify($handle) : null;
        $data = $signed ? $this->challenges->peekTfa($signed) : null;

        if (! $data) {
            return;
        }

        $user = User::find($data['user_id']);

        if ($user) {
            $this->emailOtp->send($user);
        }
    }

    public function getStatus(User $user): array
    {
        $tf = UserTwoFactor::where('user_id', $user->id)->first();

        return [
            'enabled' => (bool) $user->two_factor_enabled,
            'totp_verified' => $tf ? (bool) $tf->verified : false,
            'backup_codes_remaining' => $tf ? count(json_decode($tf->backup_codes, true) ?: []) : 0,
        ];
    }

    /** @return array{ok: bool, message: string, data: array} */
    public function enable(Request $request, User $user, string $password): array
    {
        $account = UserAccount::where('user_id', $user->id)->where('provider_id', 'credential')->first();

        if (! $account || ! $account->password || ! Hash::check($password, $account->password)) {
            return ['ok' => false, 'message' => 'Current password is incorrect', 'data' => []];
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

        return [
            'ok' => true,
            'message' => null,
            'data' => [
                'secret' => $secret,
                'totp_uri' => $uri,
                'qr_svg' => $this->totp->qrCodeSvg($uri),
                'backup_codes' => $codes,
            ],
        ];
    }

    /** @return array{ok: bool, message: string} */
    public function verifySetup(Request $request, User $user, string $method, string $code): array
    {
        $tf = UserTwoFactor::where('user_id', $user->id)->first();

        if (! $tf) {
            return ['ok' => false, 'message' => 'Two-factor setup not started'];
        }

        $valid = match ($method) {
            'totp' => $this->totp->verify($tf->secret, $code),
            'otp' => $this->emailOtp->verify($user->email, $code)['valid'],
            default => false,
        };

        if (! $valid) {
            return ['ok' => false, 'message' => 'Invalid code'];
        }

        $tf->update(['verified' => true]);
        $user->update(['two_factor_enabled' => true]);
        $this->sessions->invalidateUserCache($user->id);
        $this->activity->log($request, $user->id, UserActivity::TFA_ENABLED);

        return ['ok' => true, 'message' => 'Two-factor authentication enabled'];
    }

    /** @return array{ok: bool, message: string} */
    public function disable(Request $request, User $user, string $password): array
    {
        $account = UserAccount::where('user_id', $user->id)->where('provider_id', 'credential')->first();

        if (! $account || ! $account->password || ! Hash::check($password, $account->password)) {
            return ['ok' => false, 'message' => 'Current password is incorrect'];
        }

        UserTwoFactor::where('user_id', $user->id)->delete();
        $user->update(['two_factor_enabled' => false]);
        $this->sessions->invalidateUserCache($user->id);
        $this->devices->revokeAll($user->id);
        $this->activity->log($request, $user->id, UserActivity::TFA_DISABLED);

        return ['ok' => true, 'message' => 'Two-factor authentication disabled'];
    }

    /** Removes only the authenticator app, keeping email-OTP/backup-code 2FA available. */
    public function removeAuthenticator(Request $request, User $user): array
    {
        $tf = UserTwoFactor::where('user_id', $user->id)->first();

        if (! $tf) {
            return ['ok' => false, 'message' => 'Authenticator app is not set up'];
        }

        $tf->update(['verified' => false]);
        $this->activity->log($request, $user->id, UserActivity::TFA_AUTHENTICATOR_REMOVED);

        return ['ok' => true, 'message' => 'Authenticator app removed'];
    }

    /** @return array{ok: bool, message: string, codes: string[]} */
    public function regenerateBackupCodes(Request $request, User $user): array
    {
        $tf = UserTwoFactor::where('user_id', $user->id)->first();

        if (! $tf) {
            return ['ok' => false, 'message' => 'Two-factor authentication is not enabled', 'codes' => []];
        }

        $codes = $this->backupCodes->generate();
        $tf->update(['backup_codes' => json_encode($this->backupCodes->hash($codes))]);
        $this->activity->log($request, $user->id, UserActivity::BACKUP_CODES_REGENERATED);

        return ['ok' => true, 'message' => 'Backup codes regenerated', 'codes' => $codes];
    }
}

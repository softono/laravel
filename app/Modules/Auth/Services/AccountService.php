<?php

namespace App\Modules\Auth\Services;

use App\Constants\UserActivity;
use App\Constants\UserRole;
use App\Constants\UserStatus;
use App\Helpers\ApiResult;
use App\Helpers\General;
use App\Models\Auth\User;
use App\Repositories\Auth\UserAccountRepository;
use App\Repositories\Auth\UserRepository;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Registration, email verification, forgot/reset password, and the
 * email-OTP sign-in flow's OTP dispatch.
 */
class AccountService
{
    public function __construct(
        protected OtpService $otp,
        protected SessionService $sessions,
        protected ActivityService $activity,
        protected UserRepository $users,
        protected UserAccountRepository $userAccounts,
    ) {}

    /**
     * `data` carries the new `user` model for the controller; it is never sent to the client as is.
     *
     * @return array{http_status: int, status: int, message: string, data: array{user?: User, requires_verification?: bool}}
     */
    public function register(Request $request, array $data): array
    {
        $email = strtolower(trim($data['email']));

        if ($this->users->findByEmail($email)) {
            return ApiResult::failure('Email already registered');
        }

        $user = $this->users->create([
            'email' => $email,
            'email_verified' => false,
            'role' => UserRole::USER,
            'status' => UserStatus::ACTIVE,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'country' => $data['country'] ?? 'India',
            'timezone' => $data['timezone'] ?? 'UTC',
            'registered_ip' => $request->ip(),
        ]);

        $this->userAccounts->create([
            'user_id' => $user->id,
            'account_id' => $user->id,
            'provider_id' => 'credential',
            'password' => Hash::make($data['password']),
        ]);

        $this->activity->log($request, $user->id, UserActivity::REGISTER);

        if (config('setting.user_email_verify') == 1) {
            $this->sendOtp('verify', $user);

            return ApiResult::success('', ['user' => $user, 'requires_verification' => true]);
        }

        return ApiResult::success('Registered successfully', ['user' => $user, 'requires_verification' => false]);
    }

    public function sendOtp(string $purpose, User $user): void
    {
        $otp = $this->otp->issue($purpose, $user->email);

        $messages = [
            'verify' => 'account verification',
            'reset' => 'password reset',
            'signin' => 'sign in',
            'tfa' => 'two-factor verification',
        ];

        (new General)->sendEmail($user->email, 'otp', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'message' => $messages[$purpose] ?? $purpose,
            'otp' => $otp,
        ]);
    }

    public function verifyAccount(Request $request, string $email, string $otp): array
    {
        $result = $this->otp->verify('verify', $email, $otp);

        if (! $result['status']) {
            return $result;
        }

        $user = $this->users->findByEmail($email);

        if (! $user) {
            return ApiResult::failure('Invalid or expired OTP', [], 422);
        }

        $this->users->update($user, ['email_verified' => true]);
        $this->sessions->invalidateUserCache($user->id);

        return ApiResult::success('Account verified successfully');
    }

    /**
     * Always returns a generic success message regardless of whether the
     * email exists - enumeration-safe.
     */
    public function forgotPassword(string $email): array
    {
        $user = $this->users->findByEmail($email);

        if ($user) {
            $this->sendOtp('reset', $user);
        }

        return ApiResult::success('If the email exists, an OTP has been sent');
    }

    public function resetPassword(Request $request, string $email, string $otp, string $newPassword): array
    {
        $result = $this->otp->verify('reset', $email, $otp);

        if (! $result['status']) {
            return $result;
        }

        $user = $this->users->findByEmail($email);

        if (! $user) {
            return ApiResult::failure('Invalid or expired OTP', [], 422);
        }

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

        $this->sessions->revokeAllForUser($user->id);
        $this->activity->log($request, $user->id, UserActivity::PASSWORD_CHANGED);

        return ApiResult::success('Password reset successfully');
    }

    /** The signed-in user as the page may see them, or a 401 failure for a guest. */
    public function sessionInfo(?User $user): array
    {
        if (! $user) {
            return ApiResult::failure('Not authenticated', [], 401);
        }

        return ApiResult::success('', [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'email_verified' => $user->email_verified,
            'two_factor_enabled' => $user->two_factor_enabled,
        ]);
    }

    /** `data.accounts`: the sign-in methods linked to the user (`credential` = a password is set). */
    public function linkedAccounts(User $user): array
    {
        return ApiResult::success('', ['accounts' => $this->userAccounts->providersFor($user->id)]);
    }

    /** Enumeration-safe: the same answer whether or not the email exists or needs verifying. */
    public function resendVerification(string $email): array
    {
        $user = $this->users->findByEmail(strtolower(trim($email)));

        if ($user && ! $user->email_verified) {
            $this->sendOtp('verify', $user);
        }

        return ApiResult::success('If the email exists, a new OTP has been sent');
    }
}

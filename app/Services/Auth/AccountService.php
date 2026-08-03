<?php

namespace App\Services\Auth;

use App\Constants\UserActivity;
use App\Constants\UserRole;
use App\Constants\UserStatus;
use App\Helpers\General;
use App\Models\Auth\User;
use App\Repositories\Auth\UserAccountRepository;
use App\Repositories\Auth\UserRepository;
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
     * @return array{ok: bool, message: ?string, next: ?string, user: ?User}
     */
    public function register(Request $request, array $data): array
    {
        $email = strtolower(trim($data['email']));

        if ($this->users->findByEmail($email)) {
            return ['ok' => false, 'message' => 'Email already registered', 'next' => null, 'user' => null];
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

            return ['ok' => true, 'message' => null, 'next' => 'verify-account', 'user' => $user];
        }

        return ['ok' => true, 'message' => 'Registered successfully', 'next' => null, 'user' => $user];
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

    /**
     * @return array{ok: bool, message: string}
     */
    public function verifyAccount(Request $request, string $email, string $otp): array
    {
        $result = $this->otp->verify('verify', $email, $otp);

        if (! $result['valid']) {
            return ['ok' => false, 'message' => $result['message']];
        }

        $user = $this->users->findByEmail($email);

        if (! $user) {
            return ['ok' => false, 'message' => 'Invalid or expired OTP'];
        }

        $this->users->update($user, ['email_verified' => true]);
        $this->sessions->invalidateUserCache($user->id);

        return ['ok' => true, 'message' => 'Account verified successfully'];
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

        return ['ok' => true, 'message' => 'If the email exists, an OTP has been sent'];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function resetPassword(Request $request, string $email, string $otp, string $newPassword): array
    {
        $result = $this->otp->verify('reset', $email, $otp);

        if (! $result['valid']) {
            return ['ok' => false, 'message' => $result['message']];
        }

        $user = $this->users->findByEmail($email);

        if (! $user) {
            return ['ok' => false, 'message' => 'Invalid or expired OTP'];
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

        return ['ok' => true, 'message' => 'Password reset successfully'];
    }
}

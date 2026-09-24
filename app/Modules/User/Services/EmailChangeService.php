<?php

namespace App\Modules\User\Services;

use App\Constants\UserActivity;
use App\Helpers\General;
use App\Models\Auth\User;
use App\Modules\Auth\Services\ChallengeService;
use App\Modules\Auth\Services\OtpService;
use App\Modules\Auth\Services\SessionService;
use App\Modules\Auth\Services\Tfa\EmailOtpMethod;
use App\Modules\Auth\Services\TfaService;
use App\Repositories\Auth\UserAccountRepository;
use App\Repositories\Auth\UserRepository;
use App\Repositories\Auth\UserTwoFactorRepository;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Changing the sign-in email takes two proofs: control of the new address
 * (an OTP mailed to it) and ownership of the account (password up front,
 * then a 2FA code or an OTP mailed to the current address). The challenge
 * lives in cache under a handle the browser echoes back; failed codes in
 * either stage share one attempt counter.
 */
class EmailChangeService
{
    protected const OTP_PURPOSE = 'email-change';

    public function __construct(
        protected ChallengeService $challenges,
        protected OtpService $otp,
        protected TfaService $tfa,
        protected EmailOtpMethod $emailOtp,
        protected SessionService $sessions,
        protected ActivityService $activity,
        protected General $general,
        protected UserRepository $users,
        protected UserAccountRepository $userAccounts,
        protected UserTwoFactorRepository $userTwoFactors,
    ) {}

    /** @return array{ok: bool, message: string, handle?: string} */
    public function start(User $user, string $password, string $newEmail): array
    {
        $account = $this->userAccounts->findCredentialAccount($user->id);

        if (! $account || ! $account->password || ! Hash::check($password, $account->password)) {
            return ['ok' => false, 'message' => 'Current password is incorrect'];
        }

        $newEmail = strtolower(trim($newEmail));

        if ($newEmail === strtolower($user->email)) {
            return ['ok' => false, 'message' => 'New email is the same as your current email'];
        }

        if ($this->users->findByEmail($newEmail)) {
            return ['ok' => false, 'message' => 'Email already in use'];
        }

        $handle = $this->challenges->createEmailChange($user->id, $newEmail);
        $this->sendNewEmailOtp($user, $newEmail);

        return ['ok' => true, 'message' => 'Verification code sent to your new email', 'handle' => $handle];
    }

    /** @return array{ok: bool, message: string, restart?: bool} */
    public function resend(User $user, string $handle): array
    {
        $pending = $this->pending($user, $handle);

        if (! $pending || $pending['new_email_verified']) {
            return $this->expired();
        }

        $this->sendNewEmailOtp($user, $pending['new_email']);

        return ['ok' => true, 'message' => 'Verification code sent to your new email'];
    }

    /** @return array{ok: bool, message: string, methods?: string[], restart?: bool} */
    public function verifyNewEmail(User $user, string $handle, string $code): array
    {
        $pending = $this->pending($user, $handle);

        if (! $pending) {
            return $this->expired();
        }

        $result = $this->otp->verify(self::OTP_PURPOSE, $pending['new_email'], $code);

        if (! $result['valid']) {
            return $this->failure($handle, $result['message']);
        }

        $this->challenges->markEmailChangeVerified($handle, $pending);

        $record = $this->userTwoFactors->findByUserId($user->id);
        $methods = ['otp'];

        if ($record?->verified) {
            $methods[] = 'totp';
        }

        if ($record) {
            $methods[] = 'backup';
        }

        return ['ok' => true, 'message' => 'New email verified', 'methods' => $methods];
    }

    /** Mails a code to the CURRENT address, for the `otp` proof in the second stage. */
    public function sendCurrentEmailOtp(User $user): void
    {
        $this->emailOtp->send($user);
    }

    /** @return array{ok: bool, message: string, restart?: bool} */
    public function verify(Request $request, User $user, string $handle, string $method, string $code): array
    {
        $pending = $this->pending($user, $handle);

        if (! $pending || ! $pending['new_email_verified']) {
            return $this->expired();
        }

        $result = $this->tfa->verifyByMethod($user, $method, $code);

        if (! $result['valid']) {
            return $this->failure($handle, $result['message'] ?? 'Invalid code');
        }

        $this->challenges->consumeEmailChange($handle);

        // The address may have been registered by someone else since the challenge started.
        if ($this->users->findByEmail($pending['new_email'])) {
            return ['ok' => false, 'message' => 'Email already in use', 'restart' => true];
        }

        $old = $user->email;
        $this->users->update($user, ['email' => $pending['new_email'], 'email_verified' => true]);
        $this->sessions->invalidateUserCache($user->id);
        $this->activity->log($request, $user->id, UserActivity::EMAIL_UPDATE, [
            'old' => ['email' => $old],
            'new' => ['email' => $pending['new_email']],
        ]);

        return ['ok' => true, 'message' => 'Email updated successfully'];
    }

    /** @return array{user_id: string, new_email: string, new_email_verified: bool}|null */
    protected function pending(User $user, string $handle): ?array
    {
        $pending = $this->challenges->peekEmailChange($handle);

        return $pending && $pending['user_id'] === $user->id ? $pending : null;
    }

    protected function sendNewEmailOtp(User $user, string $newEmail): void
    {
        $this->general->sendEmail($newEmail, 'otp', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'message' => 'email change verification',
            'otp' => $this->otp->issue(self::OTP_PURPOSE, $newEmail),
        ]);
    }

    /** Counts the failed code; once the cap is reached the challenge is destroyed and the user must restart. */
    protected function failure(string $handle, string $message): array
    {
        if ($this->challenges->bumpEmailChangeAttempts($handle)) {
            $this->challenges->consumeEmailChange($handle);

            return ['ok' => false, 'message' => 'Too many failed attempts. Please start over.', 'restart' => true];
        }

        return ['ok' => false, 'message' => $message];
    }

    protected function expired(): array
    {
        return ['ok' => false, 'message' => 'Challenge expired. Please start over.', 'restart' => true];
    }
}

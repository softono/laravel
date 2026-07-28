<?php

namespace App\Services\Auth\Tfa;

use App\Models\Auth\User;
use App\Services\Auth\AccountService;
use App\Services\Auth\OtpService;

/**
 * Email-OTP 2FA method - thin wrapper around OtpService/AccountService
 * with purpose='tfa'.
 */
class EmailOtpMethod
{
    public function __construct(
        protected OtpService $otp,
        protected AccountService $account,
    ) {}

    public function send(User $user): void
    {
        $this->account->sendOtp('tfa', $user);
    }

    /** @return array{valid: bool, message: ?string} */
    public function verify(string $email, string $code): array
    {
        return $this->otp->verify('tfa', $email, $code);
    }
}

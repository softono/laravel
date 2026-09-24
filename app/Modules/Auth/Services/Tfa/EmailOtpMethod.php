<?php

namespace App\Modules\Auth\Services\Tfa;

use App\Models\Auth\User;
use App\Modules\Auth\Services\AccountService;
use App\Modules\Auth\Services\OtpService;

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

    public function verify(string $email, string $code): array
    {
        return $this->otp->verify('tfa', $email, $code);
    }
}

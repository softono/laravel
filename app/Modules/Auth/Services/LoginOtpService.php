<?php

namespace App\Modules\Auth\Services;

use App\Helpers\ApiResult;
use App\Helpers\General;
use App\Models\Auth\User;
use App\Repositories\Auth\UserRepository;
use Illuminate\Http\Request;

/**
 * Passwordless sign-in with a code mailed to the account address. Turned on
 * by the `user_login_with_otp` setting. Sending is enumeration-safe: an
 * unknown or disabled account gets the same answer as a real one.
 */
class LoginOtpService
{
    protected const PURPOSE = 'signin';

    public function __construct(
        protected OtpService $otp,
        protected AccountService $account,
        protected AuthService $auth,
        protected LoginAttemptService $attempts,
        protected UserRepository $users,
        protected General $general,
    ) {}

    public function enabled(): bool
    {
        return config('setting.user_login_with_otp') == 1;
    }

    /**
     * Step 1. Only this step carries a captcha: a token is single-use.
     *
     * @return array{http_status: int, status: int, message: string, data: array{requires_captcha?: bool}}
     */
    public function send(string $email): array
    {
        if (! $this->enabled()) {
            return ApiResult::failure('Login with OTP is not available');
        }

        if ($this->general->recaptchaFails()) {
            return ApiResult::failure('Please complete the captcha verification', ['requires_captcha' => true]);
        }

        $user = $this->users->findByEmail(strtolower(trim($email)));

        if ($user && $user->isActive()) {
            $this->account->sendOtp(self::PURPOSE, $user);
        }

        return ApiResult::success('OTP sent');
    }

    /**
     * `data` carries the verified `user` model for the controller; it is never sent as is.
     *
     * @return array{http_status: int, status: int, message: string, data: array{user?: User, requires_verification?: bool, email?: string}}
     */
    public function verify(Request $request, string $email, string $code): array
    {
        if (! $this->enabled()) {
            return ApiResult::failure('Login with OTP is not available');
        }

        $email = strtolower(trim($email));
        $user = $this->users->findByEmail($email);

        if (! $user) {
            $this->auth->dummyPasswordCheck();

            return ApiResult::failure('Invalid email or OTP');
        }

        $result = $this->otp->verify(self::PURPOSE, $email, $code);

        if (! $result['status']) {
            return $result;
        }

        // Both checks come after the code is proven, so neither message reveals whether an email is registered.
        if (! $user->isActive()) {
            return ApiResult::failure('Account is disabled');
        }

        if (! $user->email_verified && config('setting.user_email_verify') == 1) {
            return ApiResult::failure('Please verify your email first', ['requires_verification' => true, 'email' => $user->email]);
        }

        return ApiResult::success('', ['user' => $user]);
    }
}

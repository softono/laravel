<?php

namespace App\Modules\Auth\Controllers;

use App\Constants\UserActivity;
use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\LoginOtpRequest;
use App\Modules\Auth\Services\LoginOtpService;

class LoginOtpController extends Controller
{
    public function __construct(
        protected LoginOtpService $loginOtp,
        protected LoginController $login,
    ) {
        parent::__construct();
    }

    /** Step 1 mails the code; step 2 exchanges it for a session (or a 2FA challenge). */
    public function handle(LoginOtpRequest $request)
    {
        if (! $this->loginOtp->enabled()) {
            return Response::sendMessage('Login with OTP is not available', 0);
        }

        if ((int) $request->input('step') === 1) {
            // Only the first step carries a captcha: a token is single-use.
            if ($this->general->recaptchaFails()) {
                return Response::sendMessage('Please complete the captcha verification', 0, ['requires_captcha' => true]);
            }

            $this->loginOtp->send((string) $request->input('email'));

            return Response::sendMessage('OTP sent');
        }

        $result = $this->loginOtp->verify($request, (string) $request->input('email'), (string) $request->input('otp'));

        if (! $result['ok']) {
            return Response::sendMessage($result['message'], 0, $result['data']);
        }

        $user = $result['user'];

        return $this->login->completeLogin(
            $request,
            $user,
            $request->boolean('remember'),
            (bool) $user->two_factor_enabled,
            UserActivity::LOGIN_WITH_OTP,
        );
    }
}

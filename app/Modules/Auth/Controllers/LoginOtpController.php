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
        if ((int) $request->input('step') === 1) {
            return Response::sendResult($this->loginOtp->send((string) $request->input('email')));
        }

        $result = $this->loginOtp->verify($request, (string) $request->input('email'), (string) $request->input('otp'));

        if (! $result['status']) {
            return Response::sendResult($result);
        }

        $user = $result['data']['user'];

        return $this->login->completeLogin(
            $request,
            $user,
            $request->boolean('remember'),
            (bool) $user->two_factor_enabled,
            UserActivity::LOGIN_WITH_OTP,
        );
    }
}

<?php

namespace App\Modules\Auth\Controllers;

use App\Helpers\Response;
use App\Helpers\SafeRedirect;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\VerifyAccountRequest;
use App\Modules\Auth\Services\AccountService;
use Illuminate\Http\Request;

class VerifyController extends Controller
{
    public function __construct(protected AccountService $account)
    {
        parent::__construct();
    }

    /** GET /verify - the 2FA method picker. */
    public function show(Request $request)
    {
        return view('modules.auth.verify-tfa', ['redirectPath' => SafeRedirect::path($request->query('redirect'), '/dashboard')]);
    }

    /** GET /verify-account - email verification OTP screen. */
    public function showAccount(Request $request)
    {
        $email = $request->query('code') ? base64_decode($request->query('code')) : null;

        return view('modules.auth.verify-account', ['email' => $email]);
    }

    public function verifyAccount(VerifyAccountRequest $request)
    {
        return Response::sendResult($this->account->verifyAccount(
            $request,
            $request->string('email'),
            $request->string('otp'),
        ));
    }

    /**
     * Generic OTP resend. Only 'verify' is wired up (email verification
     * resend); 'reset'/'signin'/'tfa' purposes are dispatched by their own
     * flows (forgot-password, login-otp, tfa/send-otp respectively).
     * Enumeration-safe: always a generic success message.
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'purpose' => ['nullable', 'in:verify'],
        ]);

        return Response::sendResult($this->account->resendVerification($request->string('email')));
    }
}

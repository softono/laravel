<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyAccountRequest;
use App\Models\Auth\User;
use App\Services\Auth\AccountService;
use Illuminate\Http\Request;

class VerifyController extends Controller
{
    public function __construct(protected AccountService $account)
    {
        parent::__construct();
    }

    /** GET /verify - the 2FA method picker. */
    public function show()
    {
        return view('auth.verify-tfa');
    }

    /** GET /verify-account - email verification OTP screen. */
    public function showAccount(Request $request)
    {
        $email = $request->query('code') ? base64_decode($request->query('code')) : null;

        return view('auth.verify-account', ['email' => $email]);
    }

    public function verifyAccount(VerifyAccountRequest $request)
    {
        $result = $this->account->verifyAccount(
            $request,
            $request->string('email'),
            $request->string('otp'),
        );

        if (! $result['ok']) {
            return Response::sendError(422, $result['message']);
        }

        return Response::sendMessage($result['message']);
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

        $user = User::where('email', strtolower(trim($request->string('email'))))->first();

        if ($user && ! $user->email_verified) {
            $this->account->sendOtp('verify', $user);
        }

        return Response::sendMessage('If the email exists, a new OTP has been sent');
    }
}

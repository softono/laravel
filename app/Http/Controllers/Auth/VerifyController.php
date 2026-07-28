<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\VerifyAccountRequest;
use App\Models\Auth\User;
use App\Services\Auth\AccountService;
use App\Helpers\ApiResult;
use Illuminate\Http\Request;

class VerifyController extends Controller
{
    public function __construct(protected AccountService $account)
    {
        parent::__construct();
    }

    /**
     * GET /verify - the 2FA method picker (wired fully in Phase 5).
     * Deliberately a different view than resources/views/auth/verify.blade.php,
     * which is still owned by the legacy GET auth/verify route
     * (App\Http\Controllers\AuthController@verify) until Phase 7's cutover.
     */
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
            return ApiResult::failure($result['message'])->toResponse();
        }

        return ApiResult::success($result['message'])->toResponse();
    }

    /**
     * Generic OTP resend - mirrors Next's POST /api/auth/otp. Only
     * 'verify' is wired up in this phase (email verification resend);
     * 'reset'/'signin'/'tfa' purposes are dispatched by their own flows
     * (forgot-password, login-otp, tfa/send-otp respectively).
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

        return ApiResult::success('If the email exists, a new OTP has been sent')->toResponse();
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\AccountService;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function __construct(protected AccountService $account)
    {
        parent::__construct();
    }

    public function showForgot()
    {
        return view('auth.password-forgot');
    }

    public function showReset()
    {
        return view('auth.reset-password');
    }

    public function forgot(ForgotPasswordRequest $request)
    {
        $result = $this->account->forgotPassword($request->string('email'));
        return Response::sendResult($result);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $result = $this->account->resetPassword(
            $request,
            $request->string('email'),
            $request->string('otp'),
            (string) $request->input('password'),
        );

        return Response::sendResult($result);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
            'confirm_password' => ['required', 'same:password'],
        ]);

        $user = $request->user();

        $result = app(AuthService::class)->changePassword(
            $request,
            $user,
            (string) $request->input('current_password'),
            (string) $request->input('password'),
        );
        return Response::sendResult($result);
    }
}
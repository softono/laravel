<?php

namespace App\Modules\Auth\Controllers;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\ForgotPasswordRequest;
use App\Modules\Auth\Requests\ResetPasswordRequest;
use App\Modules\Auth\Services\AccountService;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function __construct(protected AccountService $account)
    {
        parent::__construct();
    }

    public function showForgot()
    {
        return view('modules.auth.password-forgot');
    }

    public function showReset()
    {
        return view('modules.auth.reset-password');
    }

    public function forgot(ForgotPasswordRequest $request)
    {
        return Response::sendResult($this->account->forgotPassword($request->string('email')));
    }

    public function reset(ResetPasswordRequest $request)
    {
        return Response::sendResult($this->account->resetPassword(
            $request,
            $request->string('email'),
            $request->string('otp'),
            (string) $request->input('password'),
        ));
    }

    public function setPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'max:100'],
            'confirm_password' => ['required', 'same:password'],
        ]);

        return Response::sendResult(app(AuthService::class)->setPassword($request, $request->user(), (string) $request->input('password')));
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
            'confirm_password' => ['required', 'same:password'],
        ]);

        return Response::sendResult(app(AuthService::class)->changePassword(
            $request,
            $request->user(),
            (string) $request->input('current_password'),
            (string) $request->input('password'),
        ));
    }
}

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
        $result = $this->account->forgotPassword($request->string('email'));

        if (! $result['ok']) {
            return Response::sendError(422, $result['message']);
        }

        return Response::sendMessage($result['message']);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $result = $this->account->resetPassword(
            $request,
            $request->string('email'),
            $request->string('otp'),
            (string) $request->input('password'),
        );

        if (! $result['ok']) {
            return Response::sendError(422, $result['message']);
        }

        return Response::sendMessage($result['message']);
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

        if (! $result['ok']) {
            return Response::sendError(422, $result['message']);
        }

        return Response::sendMessage($result['message']);
    }
}

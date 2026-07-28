<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AccountService;
use App\Helpers\ApiResult;

class RegisterController extends Controller
{
    public function __construct(
        protected AccountService $account,
        protected LoginController $login,
    ) {
        parent::__construct();
    }

    public function show()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->account->register($request, $request->validated());

        if (! $result['ok']) {
            return ApiResult::failure($result['message'])->toResponse();
        }

        if ($result['next'] === 'verify-account') {
            return ApiResult::success(null, [
                'next' => 'verify-account',
                'email' => $result['user']->email,
            ])->toResponse();
        }

        // No email verification required - sign the new user straight in.
        return $this->login->issueSessionResponse($request, $result['user'], false);
    }
}

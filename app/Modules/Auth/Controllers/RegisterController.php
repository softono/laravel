<?php

namespace App\Modules\Auth\Controllers;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Services\AccountService;

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
        return view('modules.auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->account->register($request, $request->validated());

        if (! $result['ok']) {
            return Response::sendMessage($result['message'], 0);
        }

        if ($result['requires_verification']) {
            return Response::sendData(['requires_verification' => true, 'email' => $result['user']->email]);
        }

        // No email verification required - sign the new user straight in.
        return $this->login->issueSessionResponse($request, $result['user'], false);
    }
}

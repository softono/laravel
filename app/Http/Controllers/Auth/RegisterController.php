<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AccountService;

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
        if (! $result['status']) {
            return Response::sendResult($result);
        }
        if ($result['next'] === 'verify-account') {
            return Response::sendData([
                'next' => 'verify-account',
                'email' => $result['user']->email,
            ]);
        }

        // No email verification required - sign the new user straight in.
        return $this->login->issueSessionResponse($request, $result['user'], false);
    }
}

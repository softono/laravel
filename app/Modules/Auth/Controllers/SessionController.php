<?php

namespace App\Modules\Auth\Controllers;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\AccountService;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(protected AccountService $account)
    {
        parent::__construct();
    }

    /** GET /auth/session - the currently authenticated user, or a 401 for a guest. */
    public function show(Request $request)
    {
        return Response::sendResult($this->account->sessionInfo($request->user()));
    }

    /** GET /auth/list-accounts - the sign-in methods linked to the user (`credential` = password). */
    public function accounts(Request $request)
    {
        return Response::sendResult($this->account->linkedAccounts($request->user()));
    }
}

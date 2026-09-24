<?php

namespace App\Modules\Auth\Controllers;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Repositories\Auth\UserAccountRepository;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(protected UserAccountRepository $userAccounts)
    {
        parent::__construct();
    }

    /** GET /auth/session - the currently authenticated user, or a guest result. */
    public function show(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return Response::sendError(401, 'Not authenticated');
        }

        return Response::sendData([
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'email_verified' => $user->email_verified,
            'two_factor_enabled' => $user->two_factor_enabled,
        ]);
    }

    /** GET /auth/list-accounts - the sign-in methods linked to the user (`credential` = password). */
    public function accounts(Request $request)
    {
        return Response::sendData(['accounts' => $this->userAccounts->providersFor($request->user()->id)]);
    }
}

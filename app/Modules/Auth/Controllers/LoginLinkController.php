<?php

namespace App\Modules\Auth\Controllers;

use App\Helpers\Response;
use App\Helpers\SignedCookie;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\LoginLinkService;
use Illuminate\Http\Request;

class LoginLinkController extends Controller
{
    public function __construct(protected LoginLinkService $loginLinks)
    {
        parent::__construct();
    }

    public function showApprove()
    {
        return view('modules.auth.login-approve');
    }

    public function start(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        return Response::sendResult($this->loginLinks->start(
            $request,
            $request->string('email'),
            $request->boolean('remember'),
            $request->boolean('trust_device'),
        ));
    }

    /** POST /auth/tfa/send-login-link - a magic link as the second factor of the pending 2FA challenge. */
    public function startTfa(Request $request)
    {
        $cookie = $request->cookie(SignedCookie::name('tfa'));
        $handle = $cookie ? SignedCookie::verify($cookie) : null;

        if (! $handle) {
            return Response::sendMessage('No 2FA challenge found', 0);
        }

        return Response::sendResult($this->loginLinks->startTfa($request, $handle, $request->boolean('trust_device')));
    }

    public function poll(Request $request)
    {
        $request->validate([
            'request_id' => ['required', 'string'],
            'poll_token' => ['required', 'string'],
        ]);

        $result = $this->loginLinks->poll($request, $request->string('request_id'), $request->string('poll_token'));
        $data = $result['data'];

        if ($data['state'] === 'approved') {
            $ttlSeconds = $data['remember']
                ? config('auth_next.session_ttl_days.remember') * 86400
                : config('auth_next.session_ttl_days.default') * 86400;

            SignedCookie::queueRaw('session_token', $data['session_token'], $ttlSeconds);

            if ($data['tfa']) {
                SignedCookie::forget('tfa');
            }
        }

        // The session token travels only in the cookie.
        $result['data'] = ['state' => $data['state']];

        return Response::sendResult($result);
    }

    public function approveInfo(Request $request)
    {
        $request->validate(['id' => ['required', 'string'], 'token' => ['required', 'string']]);

        return Response::sendResult($this->loginLinks->approvalInfo($request->string('id'), $request->string('token')));
    }

    public function respond(Request $request)
    {
        $request->validate([
            'id' => ['required', 'string'],
            'token' => ['required', 'string'],
            'action' => ['required', 'in:approve,reject'],
        ]);

        return Response::sendResult($this->loginLinks->respond($request->string('id'), $request->string('token'), $request->string('action')));
    }
}

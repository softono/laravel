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

        $result = $this->loginLinks->start(
            $request,
            $request->string('email'),
            $request->boolean('remember'),
            $request->boolean('trust_device'),
        );

        return Response::sendData([
            'request_id' => $result['request_id'],
            'expires_at' => $result['expires_at'],
            'poll_token' => $result['poll_token'] ?? bin2hex(random_bytes(32)),
            'code' => $result['code'],
        ]);
    }

    /** POST /auth/tfa/send-login-link - a magic link as the second factor of the pending 2FA challenge. */
    public function startTfa(Request $request)
    {
        $cookie = $request->cookie(SignedCookie::name('tfa'));
        $handle = $cookie ? SignedCookie::verify($cookie) : null;

        if (! $handle) {
            return Response::sendMessage('No 2FA challenge found', 0);
        }

        $result = $this->loginLinks->startTfa($request, $handle, $request->boolean('trust_device'));

        if (! $result['ok']) {
            return Response::sendMessage($result['message'], 0);
        }

        return Response::sendData([
            'request_id' => $result['request_id'],
            'expires_at' => $result['expires_at'],
            'poll_token' => $result['poll_token'],
            'code' => $result['code'],
        ], 'Login link sent');
    }

    public function poll(Request $request)
    {
        $request->validate([
            'request_id' => ['required', 'string'],
            'poll_token' => ['required', 'string'],
        ]);

        $result = $this->loginLinks->poll($request, $request->string('request_id'), $request->string('poll_token'));

        if ($result['state'] === 'approved') {
            $ttlSeconds = $result['remember']
                ? config('auth_next.session_ttl_days.remember') * 86400
                : config('auth_next.session_ttl_days.default') * 86400;

            SignedCookie::queueRaw('session_token', $result['session_token'], $ttlSeconds);

            if ($result['tfa']) {
                SignedCookie::forget('tfa');
            }
        }

        return Response::sendData(['state' => $result['state']]);
    }

    public function approveInfo(Request $request)
    {
        $request->validate(['id' => ['required', 'string'], 'token' => ['required', 'string']]);

        $result = $this->loginLinks->approvalInfo($request->string('id'), $request->string('token'));

        if (! $result['ok']) {
            return Response::sendError(400, 'This login request is no longer valid');
        }

        return Response::sendData([
            'device_name' => $result['device_name'],
            'code' => $result['code'],
            'email' => $result['email'],
        ]);
    }

    public function respond(Request $request)
    {
        $request->validate([
            'id' => ['required', 'string'],
            'token' => ['required', 'string'],
            'action' => ['required', 'in:approve,reject'],
        ]);

        $result = $this->loginLinks->respond($request->string('id'), $request->string('token'), $request->string('action'));

        return Response::sendResult($result);
    }
}

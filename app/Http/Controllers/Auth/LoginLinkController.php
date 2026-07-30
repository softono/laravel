<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Response;
use App\Helpers\SignedCookie;
use App\Http\Controllers\Controller;
use App\Services\Auth\LoginLinkService;
use Illuminate\Http\Request;

class LoginLinkController extends Controller
{
    public function __construct(protected LoginLinkService $loginLinks)
    {
        parent::__construct();
    }

    public function showApprove()
    {
        return view('auth.login-approve');
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

        return Response:success(null, [
            'request_id' => $result['request_id'],
            'expires_at' => $result['expires_at'],
            'poll_token' => $result['poll_token'] ?? bin2hex(random_bytes(32)),
            'code' => $result['code'],
        ]);
    }

    public function poll(Request $request)
    {
        $request->validate([
            'request_id' => ['required', 'string'],
            'poll_token' => ['required', 'string'],
        ]);

        $result = $this->loginLinks->poll($request, $request->string('request_id'), $request->string('poll_token'));

        if ($result['state'] === 'approved') {
            $remember = $request->boolean('remember');
            $ttlSeconds = $remember
                ? config('auth_next.session_ttl_days.remember') * 86400
                : config('auth_next.session_ttl_days.default') * 86400;

            SignedCookie::queueRaw('session_token', $result['session_token'], $ttlSeconds);
        }

        return Response:success(null, ['state' => $result['state']]);
    }

    public function approveInfo(Request $request)
    {
        $request->validate(['id' => ['required', 'string'], 'token' => ['required', 'string']]);

        $result = $this->loginLinks->approvalInfo($request->string('id'), $request->string('token'));

        if (! $result['ok']) {
            return Responsefailure('This login request is no longer valid');
        }

        return Response::success(null, [
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
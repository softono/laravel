<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Services\Auth\TfaService;
use Illuminate\Http\Request;

class TfaController extends Controller
{
    public function __construct(protected TfaService $tfa)
    {
        parent::__construct();
    }

    // --- Login-time challenge endpoints (public, gated by the signed tfa cookie) ---

    public function methods(Request $request)
    {
        return Response::sendData(['methods' => $this->tfa->getChallengeMethods($request)]);
    }

    public function sendOtp(Request $request)
    {
        $this->tfa->sendLoginChallengeOtp($request);

        return Response::sendMessage('A verification code has been sent to your email');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'method' => ['required', 'in:totp,otp,backup'],
            'code' => ['required', 'string'],
            'trust_device' => ['nullable', 'boolean'],
        ]);

        return $this->tfa->verifyLoginChallenge(
            $request,
            $request->string('method'),
            $request->string('code'),
            $request->boolean('trust_device'),
        );
    }

    // --- Authenticated account-management endpoints ---

    public function status(Request $request)
    {
        return Response::sendData($this->tfa->getStatus($request->user()));
    }

    public function enable(Request $request)
    {
        $request->validate(['password' => ['required', 'string']]);
        $result = $this->tfa->enable($request, $request->user(), $request->string('password'));

        if (! $result['ok']) {
            return Response::sendError(422, $result['message']);
        }

        return Response::sendData(collect($result)->except(['ok', 'message'])->all(), $result['message']);
    }

    public function verifySetup(Request $request)
    {
        $request->validate([
            'method' => ['required', 'in:totp,otp'],
            'code' => ['required', 'string'],
        ]);

        $result = $this->tfa->verifySetup($request, $request->user(), $request->string('method'), $request->string('code'));

        if (! $result['ok']) {
            return Response::sendError(422, $result['message']);
        }

        return Response::sendData(collect($result)->except(['ok', 'message'])->all(), $result['message']);
    }

    public function disable(Request $request)
    {
        $request->validate(['password' => ['required', 'string']]);

        $result = $this->tfa->disable($request, $request->user(), $request->string('password'));

        if (! $result['ok']) {
            return Response::sendError(422, $result['message']);
        }

        return Response::sendData(collect($result)->except(['ok', 'message'])->all(), $result['message']);
    }

    public function removeAuthenticator(Request $request)
    {
        $result = $this->tfa->removeAuthenticator($request, $request->user());

        if (! $result['ok']) {
            return Response::sendError(422, $result['message']);
        }

        return Response::sendData(collect($result)->except(['ok', 'message'])->all(), $result['message']);
    }

    public function regenerateBackupCodes(Request $request)
    {
        $result = $this->tfa->regenerateBackupCodes($request, $request->user());

        if (! $result['ok']) {
            return Response::sendError(422, $result['message']);
        }

        return Response::sendData(collect($result)->except(['ok', 'message'])->all(), $result['message']);
    }
}

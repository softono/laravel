<?php

namespace App\Modules\Auth\Controllers;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\TfaService;
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
        return Response::sendResult($this->tfa->getChallengeMethods($request));
    }

    public function sendOtp(Request $request)
    {
        return Response::sendResult($this->tfa->sendLoginChallengeOtp($request));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'method' => ['required', 'in:totp,otp,backup'],
            'code' => ['required', 'string'],
            'trust_device' => ['nullable', 'boolean'],
        ]);

        return Response::sendResult($this->tfa->verifyLoginChallenge(
            $request,
            $request->string('method'),
            $request->string('code'),
            $request->boolean('trust_device'),
        ));
    }

    // --- Authenticated account-management endpoints ---

    public function status(Request $request)
    {
        return Response::sendResult($this->tfa->getStatus($request->user()));
    }

    public function enable(Request $request)
    {
        $request->validate(['password' => ['required', 'string']]);

        return Response::sendResult($this->tfa->enable($request, $request->user(), $request->string('password')));
    }

    public function verifySetup(Request $request)
    {
        $request->validate([
            'method' => ['required', 'in:totp,otp'],
            'code' => ['required', 'string'],
        ]);

        return Response::sendResult($this->tfa->verifySetup($request, $request->user(), $request->string('method'), $request->string('code')));
    }

    public function disable(Request $request)
    {
        $request->validate(['password' => ['required', 'string']]);

        return Response::sendResult($this->tfa->disable($request, $request->user(), $request->string('password')));
    }

    public function removeAuthenticator(Request $request)
    {
        return Response::sendResult($this->tfa->removeAuthenticator($request, $request->user()));
    }

    public function regenerateBackupCodes(Request $request)
    {
        return Response::sendResult($this->tfa->regenerateBackupCodes($request, $request->user()));
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\SignedCookie;
use App\Http\Controllers\Controller;
use App\Services\Auth\OAuthService;
use App\Services\Auth\SessionService;
use Illuminate\Http\Request;

class GoogleController extends Controller
{
    public function __construct(
        protected OAuthService $oauth,
        protected SessionService $sessions,
    ) {
        parent::__construct();
    }

    public function redirect()
    {
        return redirect()->away($this->oauth->redirectUrl());
    }

    public function callback(Request $request)
    {
        $result = $this->oauth->handleCallback($request);

        if (! $result['ok']) {
            return redirect('/login?error='.urlencode($result['message']));
        }

        $session = $this->sessions->issue($request, $result['user']->id, true);

        SignedCookie::queueRaw('session_token', $session->token, config('auth_next.session_ttl_days.remember') * 86400);

        return redirect(config('app.url'));
    }
}

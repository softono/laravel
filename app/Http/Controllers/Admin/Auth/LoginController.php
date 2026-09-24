<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Constants\UserActivity;
use App\Helpers\Response;
use App\Helpers\SignedCookie;
use App\Http\Controllers\Admin\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Auth\User;
use App\Services\Auth\AuthService;
use App\Services\Auth\DeviceService;
use App\Services\Auth\SessionService;
use App\Services\Auth\TfaService;
use Illuminate\Http\Request;

/**
 * Admin mirror of App\Http\Controllers\Auth\LoginController. Same
 * `users` table, same session mechanics - the only difference is
 * requireAdmin:true on the credential check (which also runs the timing-
 * safe dummyPasswordCheck() on a non-admin email, so this endpoint can't
 * be used to enumerate which accounts are admins) and the redirect
 * target.
 */
class LoginController extends Controller
{
    public function __construct(
        protected AuthService $auth,
        protected SessionService $sessions,
    ) {
        parent::__construct();
    }

    public function show()
    {
        return view('admin.auth.login');
    }

    public function login(LoginRequest $request)
    {

        $result = $this->auth->authenticate(
            $request,
            $request->string('email'),
            (string) $request->input('password'),
            requireAdmin: true,
        );

        if (! $result['ok']) {
            return Response::sendMessage($result['message'], 0);
        }

        /** @var User $user */
        $user = $result['user'];
        $remember = $request->boolean('remember');

        if ($result['requiresTfa'] && ! app(DeviceService::class)->isTrusted($request, $user->id)) {
            return app(TfaService::class)->startLoginChallenge($request, $user, $remember);
        }

        $session = $this->sessions->issue($request, $user->id, $remember);
        $this->auth->logSuccess($request, $user, UserActivity::LOGIN_SUCCESS);

        $ttlSeconds = $remember
            ? config('auth_next.session_ttl_days.remember') * 86400
            : config('auth_next.session_ttl_days.default') * 86400;

        SignedCookie::queueRaw('session_token', $session->token, $ttlSeconds);
        SignedCookie::forget('tfa');

        return Response::sendData(['next' => 'admin-dashboard'], 'Logged in successfully');
    }

    public function logout(Request $request)
    {
        $token = $request->cookie(SignedCookie::name('session_token'));
        $user = auth()->user();

        $this->auth->logout($token, $user?->id, $request);

        SignedCookie::forget('session_token');

        return redirect('/admin/auth/login');
    }
}

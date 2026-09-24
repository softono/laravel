<?php

namespace App\Modules\Auth\Controllers;

use App\Constants\UserActivity;
use App\Helpers\Response;
use App\Helpers\SignedCookie;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Services\AccountService;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Services\DeviceService;
use App\Modules\Auth\Services\SessionService;
use App\Modules\Auth\Services\TfaService;
use Illuminate\Http\Request;

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
        return view('modules.auth.login');
    }

    public function login(LoginRequest $request)
    {
        $result = $this->auth->authenticate(
            $request,
            $request->string('email'),
            (string) $request->input('password'),
        );

        if (! $result['ok']) {
            return Response::sendMessage($result['message'], 0);
        }

        /** @var User $user */
        $user = $result['user'];

        if (! $user->email_verified && config('setting.user_email_verify') == 1) {
            app(AccountService::class)->sendOtp('verify', $user);

            // 200, not 403: the login page reads data.requires_verification on this failure
            // path and jQuery only runs the caller's callback for 2xx responses.
            return Response::sendResponse(200, [
                'status' => 0,
                'message' => 'Please verify your account',
                'data' => [
                    'requires_verification' => true,
                    'email' => $user->email,
                ],
            ]);
        }

        $remember = $request->boolean('remember');

        if ($result['requiresTfa'] && class_exists(TfaService::class) && ! $this->deviceIsTrusted($request, $user)) {
            return app(TfaService::class)->startLoginChallenge($request, $user, $remember);
        }

        return $this->issueSessionResponse($request, $user, $remember);
    }

    public function issueSessionResponse(Request $request, User $user, bool $remember)
    {
        $session = $this->sessions->issue($request, $user->id, $remember);

        $this->auth->logSuccess($request, $user, UserActivity::LOGIN_SUCCESS);

        $ttlSeconds = $remember
            ? config('auth_next.session_ttl_days.remember') * 86400
            : config('auth_next.session_ttl_days.default') * 86400;

        SignedCookie::queueRaw('session_token', $session->token, $ttlSeconds);
        SignedCookie::forget('tfa');

        return Response::sendMessage('Logged in successfully');
    }

    protected function deviceIsTrusted(Request $request, User $user): bool
    {
        if (! class_exists(DeviceService::class)) {
            return false;
        }

        return app(DeviceService::class)->isTrusted($request, $user->id);
    }

    public function apiLogout(Request $request)
    {
        $token = $request->cookie(SignedCookie::name('session_token'));
        $user = auth()->user();

        $this->auth->logout($token, $user?->id, $request);

        SignedCookie::forget('session_token');

        return Response::sendMessage('Logged out successfully');
    }

    public function logout(Request $request)
    {
        $this->apiLogout($request);

        return redirect('/login');
    }
}

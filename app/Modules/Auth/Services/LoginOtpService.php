<?php

namespace App\Modules\Auth\Services;

use App\Models\Auth\User;
use App\Repositories\Auth\UserRepository;
use Illuminate\Http\Request;

/**
 * Passwordless sign-in with a code mailed to the account address. Turned on
 * by the `user_login_with_otp` setting. Sending is enumeration-safe: an
 * unknown or disabled account gets the same answer as a real one.
 */
class LoginOtpService
{
    protected const PURPOSE = 'signin';

    public function __construct(
        protected OtpService $otp,
        protected AccountService $account,
        protected AuthService $auth,
        protected LoginAttemptService $attempts,
        protected UserRepository $users,
    ) {}

    public function enabled(): bool
    {
        return config('setting.user_login_with_otp') == 1;
    }

    public function send(string $email): void
    {
        $user = $this->users->findByEmail(strtolower(trim($email)));

        if ($user && $user->isActive()) {
            $this->account->sendOtp(self::PURPOSE, $user);
        }
    }

    /**
     * @return array{ok: bool, message: ?string, user: ?User, data: array}
     */
    public function verify(Request $request, string $email, string $code): array
    {
        $email = strtolower(trim($email));
        $user = $this->users->findByEmail($email);

        if (! $user) {
            $this->auth->dummyPasswordCheck();

            return $this->failure('Invalid email or OTP');
        }

        $result = $this->otp->verify(self::PURPOSE, $email, $code);

        if (! $result['valid']) {
            return $this->failure($result['message'] ?? 'Invalid email or OTP');
        }

        // Both checks come after the code is proven, so neither message reveals whether an email is registered.
        if (! $user->isActive()) {
            return $this->failure('Account is disabled');
        }

        if (! $user->email_verified && config('setting.user_email_verify') == 1) {
            return $this->failure('Please verify your email first', ['requires_verification' => true, 'email' => $user->email]);
        }

        return ['ok' => true, 'message' => null, 'user' => $user, 'data' => []];
    }

    protected function failure(string $message, array $data = []): array
    {
        return ['ok' => false, 'message' => $message, 'user' => null, 'data' => $data];
    }
}

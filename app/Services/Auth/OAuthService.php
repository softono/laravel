<?php

namespace App\Services\Auth;

use App\Constants\UserActivity;
use App\Constants\UserRole;
use App\Constants\UserStatus;
use App\Models\Auth\User;
use App\Models\Auth\UserAccount;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

/**
 * Port of the Next app's src/server/modules/auth/oauth.service.ts +
 * src/server/lib/auth/google.ts, built on the already-installed
 * laravel/socialite rather than a hand-rolled OIDC flow (see the plan's
 * "Composer packages" note for why: Socialite exchanges the auth code
 * server-to-server and never consumes an id_token, so there's no JWT to
 * verify and Next's PKCE+nonce step has nothing to protect here).
 *
 * Same precedence as Next: link by (provider_id='google', account_id=sub)
 * -> else by email -> else create, and reject unverified Google emails.
 */
class OAuthService
{
    public function __construct(
        protected SessionService $sessions,
        protected ActivityService $activity,
    ) {}

    public function redirectUrl(): string
    {
        return Socialite::driver('google')->redirect()->getTargetUrl();
    }

    /**
     * @return array{ok: bool, message: ?string, user: ?User}
     */
    public function handleCallback(Request $request): array
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Google sign-in failed. Please try again.', 'user' => null];
        }

        $emailVerified = $googleUser->user['email_verified'] ?? $googleUser->user['verified_email'] ?? false;

        if (! $emailVerified) {
            return ['ok' => false, 'message' => 'Your Google account email is not verified.', 'user' => null];
        }

        $account = UserAccount::where('provider_id', 'google')->where('account_id', $googleUser->getId())->first();

        if ($account) {
            $user = User::find($account->user_id);

            if (! $user || ! $user->isActive()) {
                return ['ok' => false, 'message' => 'Account is disabled', 'user' => null];
            }

            $this->activity->log($request, $user->id, UserActivity::LOGIN_WITH_SOCIAL);

            return ['ok' => true, 'message' => null, 'user' => $user];
        }

        $email = strtolower(trim($googleUser->getEmail()));
        $user = User::where('email', $email)->first();

        if ($user) {
            if (! $user->isActive()) {
                return ['ok' => false, 'message' => 'Account is disabled', 'user' => null];
            }

            UserAccount::create([
                'user_id' => $user->id,
                'account_id' => $googleUser->getId(),
                'provider_id' => 'google',
                'access_token' => $googleUser->token ?? null,
                'refresh_token' => $googleUser->refreshToken ?? null,
                'scope' => 'openid email profile',
            ]);

            $this->activity->log($request, $user->id, UserActivity::LOGIN_WITH_SOCIAL);

            return ['ok' => true, 'message' => null, 'user' => $user];
        }

        [$firstName, $lastName] = $this->splitName($googleUser->getName() ?: $googleUser->getNickname() ?: $email);

        $user = User::create([
            'email' => $email,
            'email_verified' => true,
            'image' => $googleUser->getAvatar(),
            'role' => UserRole::USER,
            'status' => UserStatus::ACTIVE,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'timezone' => 'UTC',
            'registered_ip' => $request->ip(),
        ]);

        UserAccount::create([
            'user_id' => $user->id,
            'account_id' => $googleUser->getId(),
            'provider_id' => 'google',
            'access_token' => $googleUser->token ?? null,
            'refresh_token' => $googleUser->refreshToken ?? null,
            'scope' => 'openid email profile',
        ]);

        $this->activity->log($request, $user->id, UserActivity::REGISTER_WITH_SOCIAL);

        return ['ok' => true, 'message' => null, 'user' => $user];
    }

    /** @return array{0: string, 1: string} */
    protected function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        return [$parts[0] ?: 'Google', $parts[1] ?? 'User'];
    }
}

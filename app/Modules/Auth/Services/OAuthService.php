<?php

namespace App\Modules\Auth\Services;

use App\Constants\UserActivity;
use App\Constants\UserRole;
use App\Constants\UserStatus;
use App\Helpers\ApiResult;
use App\Models\Auth\User;
use App\Repositories\Auth\UserAccountRepository;
use App\Repositories\Auth\UserRepository;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

/**
 * Google sign-in, built on laravel/socialite rather than a hand-rolled
 * OIDC flow - Socialite exchanges the auth code server-to-server and
 * never consumes an id_token, so there's no JWT to verify and no PKCE/nonce
 * step is needed.
 *
 * Link precedence: (provider_id='google', account_id=sub) -> else by
 * email -> else create, and reject unverified Google emails.
 */
class OAuthService
{
    public function __construct(
        protected SessionService $sessions,
        protected ActivityService $activity,
        protected UserRepository $users,
        protected UserAccountRepository $userAccounts,
    ) {}

    public function redirectUrl(): string
    {
        return Socialite::driver('google')->redirect()->getTargetUrl();
    }

    /**
     * `data.user` is the signed-in `User` model, for the controller; it is never sent as is.
     *
     * @return array{http_status: int, status: int, message: string, data: array{user?: User}}
     */
    public function handleCallback(Request $request): array
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return ApiResult::failure('Google sign-in failed. Please try again.');
        }

        $emailVerified = $googleUser->user['email_verified'] ?? $googleUser->user['verified_email'] ?? false;

        if (! $emailVerified) {
            return ApiResult::failure('Your Google account email is not verified.');
        }

        $account = $this->userAccounts->findProviderAccount('google', $googleUser->getId());

        if ($account) {
            $user = $this->users->findById($account->user_id);

            if (! $user || ! $user->isActive()) {
                return ApiResult::failure('Account is disabled');
            }

            $this->activity->log($request, $user->id, UserActivity::LOGIN_WITH_SOCIAL);

            return ApiResult::success('', ['user' => $user]);
        }

        $email = strtolower(trim($googleUser->getEmail()));
        $user = $this->users->findByEmail($email);

        if ($user) {
            if (! $user->isActive()) {
                return ApiResult::failure('Account is disabled');
            }

            $this->userAccounts->create([
                'user_id' => $user->id,
                'account_id' => $googleUser->getId(),
                'provider_id' => 'google',
                'access_token' => $googleUser->token ?? null,
                'refresh_token' => $googleUser->refreshToken ?? null,
                'scope' => 'openid email profile',
            ]);

            $this->activity->log($request, $user->id, UserActivity::LOGIN_WITH_SOCIAL);

            return ApiResult::success('', ['user' => $user]);
        }

        [$firstName, $lastName] = $this->splitName($googleUser->getName() ?: $googleUser->getNickname() ?: $email);

        $user = $this->users->create([
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

        $this->userAccounts->create([
            'user_id' => $user->id,
            'account_id' => $googleUser->getId(),
            'provider_id' => 'google',
            'access_token' => $googleUser->token ?? null,
            'refresh_token' => $googleUser->refreshToken ?? null,
            'scope' => 'openid email profile',
        ]);

        $this->activity->log($request, $user->id, UserActivity::REGISTER_WITH_SOCIAL);

        return ApiResult::success('', ['user' => $user]);
    }

    /** @return array{0: string, 1: string} */
    protected function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        return [$parts[0] ?: 'Google', $parts[1] ?? 'User'];
    }
}

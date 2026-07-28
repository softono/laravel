<?php

namespace App\Services\Auth;

use App\Models\Auth\User;
use App\Models\Auth\UserSession;
use App\Helpers\ClientInfo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Port of the Next app's src/server/modules/auth/session.service.ts.
 *
 * Token = base64url(random_bytes(32)). DB TTL is 30 days (remember) or
 * 1 day (default). Sessions are cached for session_cache_ttl seconds
 * under auth:session:{token} / auth:user:{id}. Sliding refresh: if the
 * session's updated_at is older than session_update_age seconds, push
 * expires_at forward by the original lifetime - the token itself never
 * rotates.
 */
class SessionService
{
    protected function cache()
    {
        // Uses the app's normal default cache store (config('cache.default'))
        // rather than forcing a specific driver - whatever CACHE_STORE is
        // set to for the app is what backs the session cache too.
        return Cache::store();
    }

    public function issue(Request $request, string $userId, bool $remember): UserSession
    {
        $token = $this->generateToken();
        $days = $remember
            ? config('auth_next.session_ttl_days.remember')
            : config('auth_next.session_ttl_days.default');

        $session = UserSession::create([
            'token' => $token,
            'user_id' => $userId,
            'expires_at' => now()->addDays($days),
            'ip_address' => ClientInfo::ip($request),
            'user_agent' => ClientInfo::userAgent($request),
            'device_uid' => ClientInfo::deviceUid($request),
            'remember' => $remember,
        ]);

        $this->cacheSession($session);

        return $session;
    }

    /**
     * Validates a raw session token. Returns ['session' => UserSession, 'user' => User]
     * or null if the token is invalid, unknown, or expired.
     *
     * @return array{session: UserSession, user: User}|null
     */
    public function validate(?string $token): ?array
    {
        if (! $token) {
            return null;
        }

        $cacheKey = "auth:session:{$token}";
        $cached = $this->cache()->get($cacheKey);

        if ($cached) {
            $session = UserSession::find($cached['session_id']);
        } else {
            $session = UserSession::where('token', $token)->first();
        }

        if (! $session) {
            return null;
        }

        if ($session->expires_at->isPast()) {
            $this->revokeSession($session);

            return null;
        }

        $user = $this->cachedUser($session->user_id);

        if (! $user) {
            $this->revokeSession($session);

            return null;
        }

        $this->maybeSlideExpiry($session);

        if (! $cached) {
            $this->cacheSession($session);
        }

        return ['session' => $session, 'user' => $user];
    }

    public function revoke(string $token): void
    {
        $session = UserSession::where('token', $token)->first();

        if ($session) {
            $this->revokeSession($session);
        }
    }

    public function revokeAllForUser(string $userId): void
    {
        $sessions = UserSession::where('user_id', $userId)->get();

        foreach ($sessions as $session) {
            $this->revokeSession($session);
        }
    }

    public function revokeSession(UserSession $session): void
    {
        $this->cache()->forget("auth:session:{$session->token}");
        $session->delete();
    }

    protected function maybeSlideExpiry(UserSession $session): void
    {
        $updateAge = config('auth_next.session_update_age');

        if ($session->updated_at && $session->updated_at->diffInSeconds(now()) < $updateAge) {
            return;
        }

        $days = $session->remember
            ? config('auth_next.session_ttl_days.remember')
            : config('auth_next.session_ttl_days.default');

        $session->expires_at = now()->addDays($days);
        $session->save();

        $this->cacheSession($session);
    }

    protected function cacheSession(UserSession $session): void
    {
        $this->cache()->put(
            "auth:session:{$session->token}",
            ['session_id' => $session->id],
            config('auth_next.session_cache_ttl'),
        );
    }

    protected function cachedUser(string $userId): ?User
    {
        $key = "auth:user:{$userId}";

        return $this->cache()->remember($key, config('auth_next.session_cache_ttl'), function () use ($userId) {
            return User::find($userId);
        });
    }

    public function invalidateUserCache(string $userId): void
    {
        $this->cache()->forget("auth:user:{$userId}");
    }

    protected function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}

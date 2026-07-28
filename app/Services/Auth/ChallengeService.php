<?php

namespace App\Services\Auth;

use App\Helpers\SignedCookie;
use Illuminate\Support\Facades\Cache;

/**
 * 2FA and WebAuthn challenges live ONLY in cache (never the database) -
 * the cookie carries a random handle, the cache entry keyed by that
 * handle carries the actual challenge state. Also backs the
 * failed-attempt counter shared by every 2FA verification method.
 */
class ChallengeService
{
    protected function cache()
    {
        return Cache::store();
    }

    /**
     * @return string the challenge handle (goes into the signed {uid}_tfa cookie)
     */
    public function createTfa(string $userId, bool $remember): string
    {
        $handle = SignedCookie::base64UrlEncode(random_bytes(32));

        $this->cache()->put("auth:tfa:{$handle}", [
            'user_id' => $userId,
            'remember' => $remember,
        ], (int) config('auth_next.tfa_ttl'));

        return $handle;
    }

    public function peekTfa(string $handle): ?array
    {
        return $this->cache()->get("auth:tfa:{$handle}");
    }

    public function consumeTfa(string $handle): ?array
    {
        $data = $this->peekTfa($handle);

        if ($data) {
            $this->cache()->forget("auth:tfa:{$handle}");
            $this->cache()->forget("auth:tfa:attempts:{$handle}");
        }

        return $data;
    }

    /**
     * Atomically bumps the failed-attempt counter for a challenge handle.
     * Returns the new count. Destroys the challenge once
     * tfa_max_attempts is exceeded.
     */
    public function bumpTfaAttempts(string $handle): int
    {
        $key = "auth:tfa:attempts:{$handle}";

        if (! $this->cache()->has($key)) {
            $this->cache()->put($key, 0, (int) config('auth_next.tfa_ttl'));
        }

        $count = $this->cache()->increment($key);

        if ($count > (int) config('auth_next.tfa_max_attempts')) {
            $this->consumeTfa($handle);
        }

        return $count;
    }

    public function tfaAttemptsExceeded(string $handle): bool
    {
        $count = (int) $this->cache()->get("auth:tfa:attempts:{$handle}", 0);

        return $count > (int) config('auth_next.tfa_max_attempts');
    }

    /**
     * @return string the WebAuthn challenge handle (goes into the signed {uid}_wac cookie)
     */
    public function createWebAuthn(string $challenge, ?string $userId = null): string
    {
        $handle = SignedCookie::base64UrlEncode(random_bytes(32));

        $this->cache()->put("webauthn:chal:{$handle}", [
            'challenge' => $challenge,
            'user_id' => $userId,
        ], (int) config('auth_next.webauthn_ttl'));

        return $handle;
    }

    public function consumeWebAuthn(string $handle): ?array
    {
        $data = $this->cache()->get("webauthn:chal:{$handle}");

        if ($data) {
            $this->cache()->forget("webauthn:chal:{$handle}");
        }

        return $data;
    }
}

<?php

namespace App\Modules\Auth\Services;

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

    /**
     * Email-change challenge: the handle goes to the browser, the target
     * address and progress stay server-side. Stage 1 proves control of the
     * new address, stage 2 proves the account owner (2FA / email code).
     */
    public function createEmailChange(string $userId, string $newEmail): string
    {
        $handle = SignedCookie::base64UrlEncode(random_bytes(32));

        $this->cache()->put("account:email-change:{$handle}", [
            'user_id' => $userId,
            'new_email' => $newEmail,
            'new_email_verified' => false,
        ], (int) config('auth_next.tfa_ttl'));

        return $handle;
    }

    public function peekEmailChange(string $handle): ?array
    {
        return $this->cache()->get("account:email-change:{$handle}");
    }

    public function markEmailChangeVerified(string $handle, array $pending): void
    {
        $this->cache()->put("account:email-change:{$handle}", [...$pending, 'new_email_verified' => true], (int) config('auth_next.tfa_ttl'));
    }

    public function consumeEmailChange(string $handle): void
    {
        $this->cache()->forget("account:email-change:{$handle}");
        $this->cache()->forget("account:email-change:attempts:{$handle}");
    }

    /** Counts a failed attempt (shared by both stages); true once the cap is reached. */
    public function bumpEmailChangeAttempts(string $handle): bool
    {
        $key = "account:email-change:attempts:{$handle}";

        $this->cache()->add($key, 0, (int) config('auth_next.tfa_ttl'));

        return $this->cache()->increment($key) >= (int) config('auth_next.tfa_max_attempts');
    }
}

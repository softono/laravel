<?php

namespace App\Modules\Auth\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Failed-login counter per email, over a fixed window. Reaching
 * RECAPTCHA_THRESHOLD demands a captcha; LOCKOUT_THRESHOLD locks the account
 * until the window expires. Keyed by the submitted email, whether or not it
 * exists, so a lockout does not reveal which emails are registered.
 */
class LoginAttemptService
{
    public const WINDOW_SECONDS = 900;

    public const LOCKOUT_THRESHOLD = 5;

    public const RECAPTCHA_THRESHOLD = 3;

    public function recordFailure(string $email): int
    {
        $key = $this->key($email);
        Cache::add($key, 0, self::WINDOW_SECONDS);

        return (int) Cache::increment($key);
    }

    public function failureCount(string $email): int
    {
        return (int) Cache::get($this->key($email), 0);
    }

    public function clear(string $email): void
    {
        Cache::forget($this->key($email));
    }

    public function isLocked(string $email): bool
    {
        return $this->failureCount($email) >= self::LOCKOUT_THRESHOLD;
    }

    public function needsCaptcha(string $email): bool
    {
        return $this->failureCount($email) >= self::RECAPTCHA_THRESHOLD;
    }

    private function key(string $email): string
    {
        return 'login:fail:'.strtolower(trim($email));
    }
}

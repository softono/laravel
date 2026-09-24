<?php

namespace App\Helpers;

use App\Models\Auth\User;
use App\Modules\Auth\Services\SessionService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

/**
 * A real Guard backed by the signed session cookie, so Blade views and
 * controllers that call auth()->user(), Auth::id() or @auth all work
 * normally. Resolution is lazy and cached: SessionService::validate()
 * hits the cache store, not the database, on the common path.
 */
class SessionTokenGuard implements Guard
{
    protected ?User $user = null;

    protected bool $resolved = false;

    public function __construct(
        protected SessionService $sessions,
        protected Request $request,
    ) {}

    public function check(): bool
    {
        return ! is_null($this->user());
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function user(): ?Authenticatable
    {
        if ($this->resolved) {
            return $this->user;
        }

        $this->resolved = true;

        $token = $this->request->cookie(SignedCookie::name('session_token'));
        $result = $this->sessions->validate($token);

        $this->user = $result['user'] ?? null;

        return $this->user;
    }

    public function id(): int|string|null
    {
        return $this->user()?->getAuthIdentifier();
    }

    public function validate(array $credentials = []): bool
    {
        // Credential validation goes through AuthService, not the guard.
        return false;
    }

    public function hasUser(): bool
    {
        return ! is_null($this->user);
    }

    public function setUser(Authenticatable $user): static
    {
        $this->user = $user;
        $this->resolved = true;

        return $this;
    }
}

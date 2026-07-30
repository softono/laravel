# Architecture

Detailed reference for how a request moves through this application, where each kind of logic belongs, and how the legacy and current stacks coexist.

Summary in [`../AGENTS.md`](../AGENTS.md#architecture-summary).

---

## Request Lifecycle

```
HTTP Request
  │
  ├─ Global web middleware
  │    ValidatePathEncoding → InvokeDeferredCallbacks → TrustProxies
  │    → HandleCors → PreventRequestsDuringMaintenance → ValidatePostSize
  │    → TrimStrings → ConvertEmptyStringsToNull → InjectDebugbar
  │    → EncryptCookies → AddQueuedCookiesToResponse → StartSession
  │    → ShareErrorsFromSession → VerifyCsrfToken → SubstituteBindings
  │    → EnsureDeviceUid            ← appended in bootstrap/app.php
  │
  ├─ Route middleware (per route group)
  │    auth.throttle:{name}   rate limiting, fails closed
  │    auth.redirect          bounce authenticated users away from /login
  │    auth.user              require a valid session
  │    auth.admin             require a session + isAdmin() + hasPermission()
  │    admin.guest.redirect   bounce authenticated admins away from /admin/auth/login
  │
  ├─ Controller
  │    FormRequest validates → service call → Response / view
  │
  ├─ Service
  │    business logic, returns array or Response
  │
  └─ Model / Cache / DB
```

`EnsureDeviceUid` is registered **globally on the `web` group** *and* aliased as `device.uid`. Auth routes list the alias explicitly for clarity; the global registration is what guarantees every request has a device UID.

---

## Layer Responsibilities

### Controllers

Thin. A controller should read as: validate, delegate, shape the response.

```php
class PasswordController extends Controller
{
    public function __construct(protected AccountService $account)
    {
        parent::__construct();   // REQUIRED - shares $general + settings into views
    }

    public function forgot(ForgotPasswordRequest $request)
    {
        $result = $this->account->forgotPassword($request->string('email'));

        return Response::success($result['message']);
    }
}
```

Rules:

- Extend `App\Http\Controllers\Controller` (user side) or `App\Http\Controllers\Admin\Controller` (admin side). Both constructors call `General::configSettings()` and `View::share('general', …)`. Skipping `parent::__construct()` leaves every view without `$general` and without `config('setting.*')`.
- No business logic, no query building, no direct `Hash::` or `Cookie::` calls where a service or helper exists.

### Services

All business logic. Located in `app/Services/Auth/` for auth concerns.

Return either:

- a **result array** — `['ok' => bool, 'message' => ?string, ...]` — when the caller decides the HTTP shape, or
- an **`Response`/`JsonResponse`** when the service must also attach cookies (e.g. `TfaService::verifyLoginChallenge()` issues a session cookie on success).

Dependencies are constructor-injected:

```php
class LoginLinkService
{
    public function __construct(
        protected SessionService $sessions,
        protected ActivityService $activity,
        protected DeviceService $devices,
    ) {}
}
```

Service catalogue:

| Service | Responsibility |
|---|---|
| `SessionService` | Issue / validate / revoke sessions; sliding expiry; user cache |
| `AuthService` | Password credential check, logout, change/set password |
| `AccountService` | Registration, email verification, forgot/reset password |
| `OtpService` | 6-digit OTP issue + verify with atomic attempt cap |
| `ChallengeService` | Cache-only 2FA / WebAuthn challenge handles + attempt counter |
| `TfaService` | 2FA orchestration; delegates to `Tfa/{Totp,EmailOtp,BackupCode}Method` |
| `DeviceService` | Trusted devices (30-day 2FA bypass) |
| `LoginLinkService` | Magic link lifecycle: create, poll, approve/reject, claim |
| `PasskeyService` | WebAuthn registration + assertion |
| `OAuthService` | Google sign-in, account linking precedence |
| `ActivityService` | Audit log writer |

### Models

Schema, casts, relationships. The nine `App\Models\Auth\*` models contain **zero query methods** — that is deliberate and should be preserved.

```php
class UserSession extends Model
{
    use HasUuids;

    protected $table = 'user_sessions';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [...];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'remember' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
```

Legacy models (`App\Models\User`, `UserActivity`, `UserAuth`, `Setting`, `Seos`, `Pages`, `EmailTemplates`) *do* carry query methods (`list`, `listAdmin`, `store`, …). That is the pattern being migrated away from — do not extend it. A repository extraction is planned; see `docs/local/task_pending.md`.

### Helpers

Stateless utilities in `app/Helpers/`:

| Helper | Purpose |
|---|---|
| `Response` | The `{status, message, data}` envelope |
| `SignedCookie` | HMAC-signed cookie naming, signing, verification, base64url |
| `ClientInfo` | Client IP (XFF-aware), user agent, device name, device UID |
| `SessionTokenGuard` | The custom auth guard |
| `General` | Legacy grab-bag: settings, email sending, file URLs, IP location |
| `Pagination` | Legacy paginator |
| `QrGenerator` | Legacy QR generator, superseded by `bacon/bacon-qr-code` |

---

## Authentication Guard Resolution

`config/auth.php` points the `web` guard at a custom driver:

```php
'guards' => [
    'web' => ['driver' => 'session_token', 'provider' => 'users'],
],
'providers' => [
    'users' => ['driver' => 'eloquent', 'model' => App\Models\Auth\User::class],
],
```

registered in `AppServiceProvider::boot()`:

```php
Auth::extend('session_token', fn ($app, $name, array $config) =>
    new SessionTokenGuard($app->make(SessionService::class), $app['request'])
);
```

`SessionTokenGuard` resolves lazily and memoises per request:

1. Read the `{APP_UID}_session_token` cookie.
2. `SessionService::validate($token)` → session row + user, or `null`.
3. Cache the result on the guard instance.

**Consequence:** every existing `auth()->user()`, `Auth::id()`, `Auth::check()` and `@auth` in the codebase keeps working unchanged, including inside legacy controllers — they all resolve `App\Models\Auth\User`.

**Limitation:** the guard implements `Illuminate\Contracts\Auth\Guard`, **not** `StatefulGuard`. `Auth::login()`, `Auth::logout()` and `Auth::attempt()` will fatal. Use `SessionService::issue()` / `revoke()` instead. This is why admin impersonation was removed rather than ported.

---

## Session & Caching Strategy

### Two unrelated "sessions"

Do not confuse them:

| | Laravel session | Auth session |
|---|---|---|
| Table | `sessions` | `user_sessions` |
| Purpose | CSRF token, flash data | Authentication |
| Driver | `SESSION_DRIVER=database` | Custom (`SessionService`) |
| Key | `laravel_session` cookie | `{APP_UID}_session_token` cookie |

> `sessions.user_id` must be `CHAR(36)` to match UUID user ids. It ships from Laravel as `BIGINT`, which silently truncates and 500s every authenticated request — fixed by `2026_07_28_000100_fix_sessions_user_id_for_uuid.php`.

### Cache keys

| Key | TTL | Contents |
|---|---|---|
| `auth:session:{token}` | `session_cache_ttl` (300s) | `['session_id' => …]` |
| `auth:user:{id}` | `session_cache_ttl` | the `User` model |
| `auth:tfa:{handle}` | `tfa_ttl` (600s) | 2FA challenge state |
| `auth:tfa:attempts:{handle}` | `tfa_ttl` | failed-attempt counter |
| `webauthn:chal:{handle}` | `webauthn_ttl` (300s) | WebAuthn challenge |
| `setting` | 1 day | application settings |

Invalidate the user cache with `SessionService::invalidateUserCache($userId)` after mutating a user — otherwise stale role/status data persists for up to 300 seconds.

### Sliding expiry

`SessionService::maybeSlideExpiry()` pushes `expires_at` forward by the original lifetime once `updated_at` is older than `session_update_age` (86400s). **The token itself never rotates.**

> Known inefficiency: the cached path still does a primary-key DB lookup, so caching does not currently avoid a query. See `docs/local/plan_improvemtns.md`.

---

## Configuration

`config/auth_next.php` holds every auth tunable. Nothing in the auth stack should hardcode a timeout, TTL or attempt cap.

| Key | Default | Meaning |
|---|---|---|
| `encryption_key` | `ENCRYPTION_KEY` | Cookie signing key (**not** `APP_KEY`) |
| `app_uid` | `APP_UID` | Cookie name prefix |
| `session_ttl_days.remember` / `.default` | 30 / 1 | Session lifetime |
| `session_cache_ttl` | 300 | Session/user cache seconds |
| `session_update_age` | 86400 | Sliding-refresh threshold |
| `otp_expire_sec` | 600 | OTP validity |
| `otp_max_attempts` | 5 | OTP guesses before the row is destroyed |
| `login_link_expire_sec` | 300 | Magic-link validity |
| `tfa_ttl` | 600 | 2FA challenge validity |
| `tfa_max_attempts` | 5 | 2FA guesses per challenge |
| `webauthn_ttl` | 300 | WebAuthn challenge validity |
| `trust_days` | 30 | Trusted-device duration |
| `device_cookie_days` | 365 | Device UID cookie lifetime |
| `backup_code_count` | 10 | Backup codes generated |
| `totp_window` | 1 | TOTP drift tolerance (±1 × 30s step) |

> The filename's `_next` suffix is historical (the design was ported from a Next.js app) and is scheduled for renaming.

---

## The Legacy / Current Split

### Why both exist

The auth system was rebuilt in place. The new stack owns authentication; the legacy stack still owns the admin CRUD screens and parts of the account area. Rather than a big-bang cutover, the guard was made compatible so both sides resolve the same user model.

### What actually differs

| Aspect | Current | Legacy |
|---|---|---|
| User table | `users` (UUID) | `users` — repointed, but expects integer roles |
| Password | `user_accounts.password` | expected `users.password` (**gone**) |
| Sessions | `user_sessions` + signed cookie | `user_devices` via `UserAuth` |
| 2FA state | `user_two_factors` | `users.status_tfa`, `totp_secret_key` (**gone**) |
| Trusted devices | `user_devices` | `users.ignore_tfa_device` CSV (**gone**) |
| Roles | strings via `UserRole` | integers `[1,2,3]` / `[4]` |

Columns marked **gone** do not exist. Legacy code touching them either errors on write or silently reads `null`.

### Rules for working across the split

1. New auth work → `App\Services\Auth\*`, routes in `routes/auth.php`.
2. Touching a legacy controller → verify which model and columns it assumes before trusting it.
3. `routes/web.php` now gates on `auth.user` / `auth.admin` directly (the old `user`/`admin` aliases and their `UserAuth`/`AdminAuth` middleware classes were removed).
4. When porting a legacy screen, migrate its data source to the `user_*` tables in the same change — do not leave it half-converted.

Current status and remaining work: `docs/local/task_pending.md`.

---

## Adding a New Feature

**A new auth endpoint**

1. Route in `routes/auth.php` with `auth.throttle:{name}` (add the tier to `AuthRateLimit::LIMITS`).
2. FormRequest in `App\Http\Requests\Auth\` overriding `failedValidation()` to return the envelope.
3. Service method returning a result array.
4. Thin controller mapping the result to `Response`.
5. Log the outcome via `ActivityService` with a `UserActivity` constant.
6. Exercise it live before calling it done.

**A new admin screen**

1. Route in `routes/web.php` under the `['web', 'auth.admin']` group.
2. Controller extending `App\Http\Controllers\Admin\Controller`.
3. Blade view extending `admin.layouts.main`.
4. Data access through a service — do not add query methods to models.

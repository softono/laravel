# Architecture

Detailed reference for how a request moves through this application, how the code is split into modules, and where each kind of logic belongs.

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
  │    business logic, returns a result array or a Response
  │
  ├─ Repository
  │    the only layer that queries
  │
  └─ Model / Cache / DB
```

`EnsureDeviceUid` is registered **globally on the `web` group** *and* aliased as `device.uid`. Auth routes list the alias explicitly for clarity; the global registration is what guarantees every request has a device UID.

---

## Modules

Code is grouped by feature in `app/Modules/`. Each module has the same shape:

```
app/Modules/<Module>/
  Controllers/       thin HTTP layer
  Requests/          FormRequests
  Services/          business logic
  <module>_routes.php
resources/views/modules/<module>/
```

Admin-panel features are modules inside one container, `app/Modules/Admin/` (views in `resources/views/modules/admin/`). Its `Controllers/Controller.php` is the shared admin base; `Services/` holds services used by several admin modules (`AccountManagementService`, `AccountListService`).

| Module | Owns |
|---|---|
| `Auth` | Login, register, password, verify, 2FA, passkeys, magic link, Google, session endpoints; the auth services |
| `User` | The signed-in user's dashboard and account area (profile, image, sessions, activity, 2FA/passkey pages, deactivate) |
| `Page`, `Contact`, `Site`, `Blog` | Public pages, contact form, home + cron, blog |
| `Note` | The signed-in user's notes |
| `Admin/Auth` | Admin login and password pages |
| `Admin/Dashboard`, `Account`, `User`, `Admins`, `Activity`, `Device` | Dashboard, the admin's own account (a subclass of the user account controller), end-user management, admin accounts, activity log, sessions |
| `Admin/Page`, `Seo`, `Setting`, `EmailTemplate`, `Blog` | Content management |

Dependency direction: an admin module may use a top-level module's services (admin login reuses `Auth\Services\SessionService`; `Admin/Account` extends `User\Controllers\AccountController`); a top-level module never imports `App\Modules\Admin`. A service used by two or more modules lives in `app/Services/`.

### Routes

Every module owns `<module>_routes.php`, loaded by `bootstrap/app.php`:

- top-level modules: `Route::middleware('web')`, each route file states its own middleware;
- `Admin/Auth`: `web` + the `admin` prefix, guest-only (routes set their own middleware);
- every other admin module: `web` + `auth.admin` + the `admin` prefix — so an admin route file contains `Route::get('user', …)`, not `admin/user`, and a new module cannot forget the auth check.

There is no `routes/web.php` and no `/api` prefix. Route **names** stay in slash style (`admin/user/view`) because `SeoMetaRepository` matches `seos.url` against the current route name.

---

## Layer Responsibilities

### Controllers

Thin. A controller reads as: validate, delegate, shape the response.

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

        return Response::sendMessage($result['message']);
    }
}
```

Rules:

- Extend `App\Http\Controllers\Controller` (public/user side) or `App\Modules\Admin\Controllers\Controller` (admin side, which extends it). The constructor calls `General::configSettings()` and `View::share('general', …)`; skipping `parent::__construct()` leaves every view without `$general` and `config('setting.*')`.
- No business logic, no query building, no direct `Hash::` or `Cookie::` calls where a service or helper exists.
- Responses never carry navigation. The view puts `data-next` / `data-next-url` on the triggering element.

### Services

All business logic, in the owning module's `Services/` (or `app/Services/` when shared). Return either a **result array** — `['ok' => bool, 'message' => ?string, ...extra]` — that `Response::sendResult()` maps to the envelope, or a **`Response`** when the service must also attach cookies (`TfaService::verifyLoginChallenge()` issues a session cookie).

Dependencies are constructor-injected, and services never call Eloquent directly:

```php
class LoginLinkService
{
    public function __construct(
        protected SessionService $sessions,
        protected ActivityService $activity,
        protected UserLoginLinkRepository $loginLinks,
    ) {}
}
```

| Service | Module | Responsibility |
|---|---|---|
| `SessionService` | Auth | Issue / validate / revoke sessions; sliding expiry; user cache |
| `AuthService` | Auth | Password credential check, logout, change/set password |
| `AccountService` | Auth | Registration, email verification, forgot/reset password |
| `OtpService` | Auth | 6-digit OTP issue + verify with atomic attempt cap |
| `ChallengeService` | Auth | Cache-only 2FA / WebAuthn challenge handles + attempt counter |
| `TfaService` | Auth | 2FA orchestration; delegates to `Tfa/{Totp,EmailOtp,BackupCode}Method` |
| `DeviceService` | Auth | Trusted devices (30-day 2FA bypass) |
| `LoginLinkService`, `PasskeyService`, `OAuthService` | Auth | Magic link, WebAuthn, Google sign-in |
| `ProfileService`, `SessionListService`, `ActivityListService` | User | Profile/image/deactivate; session and activity DataTables (also used by the admin modules) |
| `AccountManagementService`, `AccountListService` | Admin | Create/update/status/delete accounts scoped to one role; user and admin list rows |
| `DashboardService`, `SitemapService`, `BlogService`, … | Admin/* | Feature logic |
| `ActivityService`, `PermissionService`, `EmailTemplateService` | shared | Audit log, admin permission tree, template rendering |

### Repositories

`app/Repositories/` is the only place that queries. One repository per table or aggregate — not per module, because modules share tables (`users` is read by Auth, User and Dashboard). The auth tables keep `Repositories/Auth/`. Repositories return models, collections, paginators or scalars — never HTML or the envelope — and DataTables methods return `Helpers\Pagination::getDataTable()` payloads for services to format.

Scoped lookups are the security boundary: `UserRepository::findByIdAndRole()`, `NoteRepository::findForUser()`, `UserSessionRepository::findForUser()`.

### Models

Schema, casts, relationships — no query methods, anywhere. The content models (`Page`, `SeoMeta`, `EmailTemplate`, `Setting`, `ContactMessages`, `Blog`, `Note`) set `$timestamps = false` because the tables mirror Next's (`created_at` / `updated_at` are filled by database defaults, `ON UPDATE` on the content tables).

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

### Helpers

Stateless utilities in `app/Helpers/`:

| Helper | Purpose |
|---|---|
| `Response` | The `{status, message, data}` envelope (Next's `response.ts` function names) |
| `SignedCookie` | HMAC-signed cookie naming, signing, verification, base64url |
| `ClientInfo` | Client IP (XFF-aware), user agent, device name, device UID |
| `SessionTokenGuard` | The custom auth guard (also exposes the current session) |
| `General` | Settings access, email sending, file upload/URLs, date formatting, reCAPTCHA check |
| `Pagination` | DataTables server-side paging and ordering |

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

**Consequence:** every existing `auth()->user()`, `Auth::id()`, `Auth::check()` and `@auth` in the codebase works in every controller and view — they all resolve `App\Models\Auth\User`.

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

## History

The app started as a Bootstrap/jQuery Laravel project with integer role codes and a `user` table, and an auth system rebuilt in place alongside it. That migration is finished: every screen now reads the Next-shaped schema through repositories, and the legacy models, services and controllers are gone. If you find a reference to `App\Models\User`, `UserAuth`, `status_tfa`, `ignore_tfa_device` or integer roles, it is a bug.

---

## Adding a New Feature

**A new module** (public or admin)

1. `app/Modules/<Module>/` (or `app/Modules/Admin/<Module>/`) with `Controllers/`, `Requests/`, `Services/` and `<module>_routes.php`. Admin route files are relative to the admin group.
2. Queries go in a repository (`app/Repositories/`); models stay schema-only.
3. Views in `resources/views/modules/<module>/`, built from the `x-ui.*` components.
4. A permission group in `PermissionService` for admin screens, and a sidebar link.
5. Forms and buttons declare their follow-up with `data-next` / `data-next-url`.
6. Regenerate the autoloader, run Pint, and exercise the endpoints on the running site.

**A new public endpoint**

Add the route with `auth.throttle:{name}` (add the tier to `AuthRateLimit::LIMITS`) or `throttle:5,15` for forms; validate in a FormRequest; return a result array; log security-relevant outcomes with `ActivityService` and a `UserActivity` constant.

# AGENTS.md

Working guide for AI coding agents on this repository. Read this before making changes.

---

## Project Overview

A Laravel 12 application with a **public marketing front-end**, a **user account area**, and a **Bootstrap admin panel**. Its defining feature is a hand-rolled authentication system supporting password login, email-OTP verification, TOTP/email/backup-code 2FA with trusted devices, magic login links with second-device approval, WebAuthn passkeys, and Google OAuth.

**The single most important thing to know:** this repo is **mid-migration**. A modern auth stack (`App\*\Auth\*`) lives alongside a legacy one (`App\Services\AuthService`, `App\Models\User`). Both are wired and running. Many class names exist twice in different namespaces. See [Legacy vs. Current](#legacy-vs-current-critical) — getting this wrong is the most common way to break the app.

---

## Tech Stack

| Layer | Choice |
|---|---|
| Framework | Laravel 12, PHP 8.2+ (running 8.4) |
| Database | MariaDB / MySQL (`DB_CONNECTION=mysql`) |
| Session & Cache | `database` driver (both) |
| Views | Blade + Bootstrap 5 (vendored theme in `public/theme/`) |
| Browser JS | **Vanilla JS**, plain `<script>` tags — no bundler for app code |
| Password hashing | argon2id (m=65536, t=3, p=4) |
| 2FA | `pragmarx/google2fa` + `bacon/bacon-qr-code` |
| Passkeys | `web-auth/webauthn-lib` v5 |
| OAuth | `laravel/socialite` (Google) |
| Formatting | Laravel Pint |
| Tests | PHPUnit 11 |

> `tailwindcss` and `@tailwindcss/vite` are in `package.json` but **not active** — no Tailwind plugin in `vite.config.js`, no `@import "tailwindcss"` in any CSS. Do not write Tailwind classes; this project is Bootstrap.

---

## Repository Structure

```
app/
  Constants/          UserRole, UserStatus, UserActivity (string constants)
  Helpers/            Response, SignedCookie, ClientInfo, SessionTokenGuard,
                      General, Pagination, QrGenerator (legacy)
  Http/
    Controllers/
      Auth/           Current auth: Login, Register, Password, Verify, Tfa,
                      Passkey, LoginLink, Google, Session
      Account/        User account area
      Admin/          Admin panel CRUD
      Admin/Auth/     Admin login + auth pages
    Middleware/       See "Middleware" below
    Requests/Auth/    FormRequests for auth endpoints
  Models/
    Auth/             Current: User + 8 auth tables (UUID PKs)
    *.php             Legacy models (see warning below)
  Services/
    Auth/             Current auth services + Tfa/ method classes
    *.php             Legacy services (see warning below)
routes/
  web.php             Front-end, account area, admin panel
  auth.php            All authentication routes
resources/views/
  layouts/            blank (auth pages), main (app shell)
  auth/  admin/  account/  common/  front/  email/
public/assets/js/     Hand-written vanilla JS (NOT built by Vite)
docs/                 Detailed documentation (see References)
docs/local/           Working notes: pending tasks, improvement & security plans
```

---

## Architecture Summary

Request flow:

```
Request
  → web middleware (+ EnsureDeviceUid, appended globally)
  → route middleware (auth.user / auth.admin / auth.throttle / auth.redirect)
  → Controller  (thin: validate → call service → return Response)
  → Service     (all business logic lives here)
  → Model       (Eloquent; schema + relationships only)
```

**Layering rule:** controllers stay thin. Business logic belongs in `App\Services\Auth\*`. Models should not accumulate query methods — new query logic goes in a service (a repository extraction is planned; see `docs/local/task_pending.md`).

Authentication is resolved by a **custom guard** (`SessionTokenGuard`) rather than Laravel's session auth, so `auth()->user()`, `Auth::id()` and `@auth` work everywhere while the actual session lives in the `user_sessions` table keyed by a signed cookie.

For request lifecycle, layer boundaries, the legacy/current split, and caching strategy → **[`docs/architecture.md`](docs/architecture.md)**

---

## Legacy vs. Current (CRITICAL)

Several classes exist twice. **Always confirm which namespace you are importing.**

| Concern | ✅ Current | ⚠️ Legacy |
|---|---|---|
| User model | `App\Models\Auth\User` | `App\Models\User` |
| Auth service | `App\Services\Auth\AuthService` | `App\Services\AuthService` |
| 2FA service | `App\Services\Auth\TfaService` | `App\Services\TfaService` |
| Account service | `App\Services\Auth\AccountService` | `App\Services\AccountService` |

Facts you need:

- **The guard resolves `App\Models\Auth\User`.** So `auth()->user()` always returns the *current* model, even inside legacy controllers.
- **`App\Models\User` (legacy) now points at the same `users` table** but still uses **integer role codes** (`[1,2,3]`) while the column stores strings (`'ADMIN'`). Its list queries silently return zero rows. Do not copy its role logic.
- **`routes/web.php` now gates every non-auth page with `auth.user` / `auth.admin`** (the old `user`/`admin` aliases and their `UserAuth`/`AdminAuth` middleware classes have been removed). They work because they call `Auth::user()`, which resolves the current model.
- The `users` table has **no** `password`, `otp`, `status_tfa`, `totp_secret_key`, `backup_code`, or `ignore_tfa_device` columns. Passwords live in `user_accounts`. Legacy code touching those columns is broken — treat it as a bug, not a pattern.

---

## Development Workflow

1. **Read before writing.** Grep for existing patterns; this codebase has strong local conventions that differ from stock Laravel.
2. **Check the namespace** against the table above before importing anything auth-related.
3. Make the change.
4. **Format:** `./vendor/bin/pint`
5. **Verify it actually works** — start the server and exercise the route. Static checks will not catch the failure modes this repo has (see Pitfalls).
6. If you moved or renamed a class: **`php composer.phar dump-autoload`**.

---

## Build / Test / Lint Commands

```bash
# Dev server
php artisan serve

# Everything at once (server + queue + logs + vite)
php composer.phar run dev

# Format (run before finishing any task)
./vendor/bin/pint
./vendor/bin/pint --test          # check only, no writes

# Tests
php artisan test
php artisan test --filter=SomeTest

# Frontend CSS (only needed if you edit resources/css/*)
npm run dev
npm run build
```

> ⚠️ `composer` is **not on PATH** in this environment. Use `php composer.phar …`.
>
> ⚠️ `phpunit.xml` has the SQLite lines **commented out**, so `php artisan test` runs against the **real MySQL database**. Uncomment them or point `DB_DATABASE` at a scratch database before running the suite.

---

## Common Commands

```bash
php artisan route:list --path=api/auth    # inspect auth endpoints
php artisan migrate                       # run migrations
php artisan db:seed --class="Database\Seeders\AuthSeeder"
php artisan tinker
php artisan optimize:clear                # config + cache + views + routes
php artisan cache:clear                   # settings are cached under key 'setting'
php artisan view:clear                    # after editing Blade
```

---

## Database Overview

MySQL/MariaDB. Migrations exist **only** for the auth tables; other tables (`settings`, `pages`, `seos`, `blogs`, `email_templates`, `notes`, `contact_messages`) predate migrations and live only in the database.

**Auth tables (9)** — all UUID `char(36)` primary keys:

| Table | Holds |
|---|---|
| `users` | identity, role, status, profile |
| `user_accounts` | one row per provider (`credential` holds the password, or `google`) |
| `user_sessions` | active sessions (token → user) |
| `user_two_factors` | TOTP secret (encrypted) + backup codes (JSON) |
| `user_devices` | trusted devices (skip 2FA for 30 days) |
| `user_verifications` | OTP store, keyed `"{purpose}:{email}"` |
| `user_login_links` | magic-link requests |
| `user_passkeys` | WebAuthn credentials |
| `user_activities` | audit log |

Conventions:

- **UUID PKs** via `HasUuids`; `$keyType = 'string'`, `$incrementing = false`.
- **`ascii_bin` collation** on every token / UUID / base64url column. This is security-critical — a case-insensitive collation would collapse the session-token keyspace. Never relax it.
- **`DATETIME`, not `TIMESTAMP`** (no 2038 limit, no implicit TZ conversion). Keep `APP_TIMEZONE=UTC`.
- Roles are **strings**: `USER`, `ADMIN`, `SUPER_ADMIN` (`App\Constants\UserRole`). Status: `active` / `inactive`.

---

## API Overview

All JSON endpoints return the same envelope:

```json
{ "status": 1, "message": "…", "data": {} }
```

`status` is `1` (success) or `0` (failure) — **not** the HTTP status.

Key conventions:

- Build responses with `Response::sendMessage()` / `Response::sendError()`, then ``.
- **HTTP status is always 200**, deliberately. The jQuery helper in `public/assets/js/app.js` only routes 2xx to the caller's callback, and pages need `data.next` on the failure path too.
- Validation via FormRequests in `App\Http\Requests\Auth\` — they render errors into the same envelope.
- Rate limiting via `auth.throttle:{name}` (see `AuthRateLimit::LIMITS`).
- CSRF applies to every POST (all auth endpoints live in the `web` group).

For endpoint listings, error handling, rate-limit tiers and examples → **[`docs/api.md`](docs/api.md)**

---

## Authentication Overview

Session-cookie based, not Laravel's built-in auth:

- Login verifies the password against `user_accounts` (argon2id), then issues a row in `user_sessions` and sets a signed cookie.
- `SessionTokenGuard` resolves the user from that cookie on each request, cached ~300s.
- Cookies are named `{APP_UID}_{name}` and signed with **`ENCRYPTION_KEY`** (a separate 32-byte env var, *not* `APP_KEY`).
- Admin and user share one `users` table; admin login just adds `requireAdmin: true`.

Supported flows: password, email OTP verification, forgot/reset password, 2FA (TOTP / email OTP / backup codes) with trusted devices, magic login links, WebAuthn passkeys, Google OAuth.

For the cookie table, guard internals, middleware order, 2FA and passkey ceremonies, and OAuth precedence → **[`docs/authentication.md`](docs/authentication.md)**

---

## Frontend Overview

Server-rendered Blade + Bootstrap 5. **No SPA framework, no build step for application JS.**

- **Layouts:** `layouts/blank.blade.php` (auth pages) and `layouts/main.blade.php` (app shell, PJAX-aware). Admin mirrors both under `admin/layouts/`.
- **JS lives in `public/assets/js/`** and is included with plain `<script src="…">`. It is *not* processed by Vite.
- Use the existing helpers — `app.ajaxForm(form, cb)`, `app.ajaxPost(url, data, cb)`, `app.ajaxGet(url, cb)`, `app.showMessage(msg, type)` — which already speak the response envelope.
- New interactive widgets are **plain DOM code in an IIFE**, exposing a named global if the page needs to initialise them.
- Vite compiles **CSS only**, and only for `layouts/main.blade.php`. `resources/js/app.js` is built but never loaded by any view.

For layout selection, the JS module pattern, CSRF wiring and PJAX behaviour → **[`docs/frontend.md`](docs/frontend.md)**

---

## Backend Overview

- **Controllers** validate (via FormRequest) and delegate. They extend `App\Http\Controllers\Controller` (user) or `App\Http\Controllers\Admin\Controller` (admin) — both share `$general` and app settings into every view, so **always call `parent::__construct()`**.
- **Services** hold business logic and return plain arrays (`['ok' => bool, 'message' => string, …]`) or an `Response`. Dependencies are constructor-injected.
- **Models** define schema, casts and relationships. The `App\Models\Auth\*` models are query-free by design.
- **Config:** every auth tunable lives in `config/auth_next.php` (TTLs, attempt caps, window sizes). Never hardcode these values.

Layer responsibilities and service-by-service detail are covered in **[`docs/architecture.md`](docs/architecture.md)**.

---

## Coding Rules & Conventions

**PHP**

- PSR-12 via Pint (Laravel preset). Run `./vendor/bin/pint` before finishing.
- Constructor property promotion for dependencies:
  ```php
  public function __construct(
      protected SessionService $sessions,
      protected ActivityService $activity,
  ) {}
  ```
- Type-hint parameters and return types. Use array shapes in docblocks:
  ```php
  /** @return array{ok: bool, message: ?string, user: ?User} */
  ```
- Prefer constructor injection over `app(Foo::class)` service location.
- Use constants, never string literals, for roles/statuses/activity types:
  ```php
  $user->role === UserRole::ADMIN          // ✅
  $user->role === 'ADMIN'                  // ❌
  ```
- Comments explain **why**, not what. Do not narrate the code.

**Blade**

- Reuse existing partials in `resources/views/common/`.
- Auth pages `@extends('layouts.blank')`; admin auth `@extends('admin.layouts.blank')`.
- Follow the established card markup — `container-xxl` → `authentication-wrapper` → `.card` → `.card-body`.

**JavaScript**

- Vanilla only. **No new framework or jQuery-plugin dependency.** (jQuery already loads for Bootstrap and the `app.*` helpers; that stays.)
- Wrap in an IIFE; `'use strict'`; guard against a missing target element.
- Pass URLs in from Blade via `data-*` attributes or an `init({...})` call — never hardcode paths in JS.

---

## Naming Conventions

| Thing | Convention | Example |
|---|---|---|
| Class | `StudlyCase` | `LoginLinkService` |
| Method / variable | `camelCase` | `verifyLoginChallenge()` |
| DB table | `snake_case` plural | `user_login_links` |
| DB column | `snake_case` | `two_factor_enabled` |
| Route path | `kebab-case` | `/api/auth/verify-account` |
| Route name | matches the path | `->name('account/two-factor')` |
| Blade view | `kebab-case.blade.php` | `verify-tfa.blade.php` |
| JS file | `kebab-case.js` | `login-link.js` |
| Constant | `SCREAMING_SNAKE` | `UserActivity::LOGIN_SUCCESS` |
| Cookie | `{APP_UID}_{name}` | `demo_session_token` |
| Cache key | `colon:separated` | `auth:session:{token}` |

Service classes are named for their domain (`OtpService`, `DeviceService`), not for a layer (`OtpManager`, `OtpHelper`).

---

## Project-Specific Rules

1. **Never store a password on `users`.** Passwords live in `user_accounts` where `provider_id = 'credential'`.
2. **Never weaken `ascii_bin`** on token/UUID columns.
3. **Never sign cookies with `APP_KEY`.** Use `SignedCookie`, which uses `ENCRYPTION_KEY`.
4. **Never hardcode TTLs or attempt caps.** They live in `config/auth_next.php`.
5. **Password change and reset revoke every session**, including the current one. This is intentional — keep it, and say so in the UI.
6. **Keep auth responses enumeration-safe.** Unknown email and known email must be indistinguishable in message, shape, and (where practical) timing. Use `AuthService::dummyPasswordCheck()` on failure paths.
7. **OTP and 2FA attempt counters increment *before* comparison**, so a wrong guess always costs an attempt.
8. **Hash high-entropy tokens with SHA-256, not argon2id.** Argon2id is for passwords, OTPs and backup codes only.
9. **Rate-limit every new public auth endpoint** with `auth.throttle:{name}`.
10. **Log security-relevant actions** via `ActivityService::log()` using an `App\Constants\UserActivity` constant.

---

## Important Patterns

**Service returns a result array; the controller send the response**

```php
$result = $this->auth->changePassword($request, $user, $current, $new);
Response::sendResult($result);
```

**Issuing a session after any successful login**

```php
$session   = $this->sessions->issue($request, $user->id, $remember);
$ttlSeconds = ($remember
    ? config('auth_next.session_ttl_days.remember')
    : config('auth_next.session_ttl_days.default')) * 86400;

SignedCookie::queueRaw('session_token', $session->token, $ttlSeconds);
$this->activity->log($request, $user->id, UserActivity::LOGIN_SUCCESS);
```

> This block is currently duplicated in six places. If you add a seventh login path, extract a shared helper instead — see `docs/local/plan_improvemtns.md`.

**Cache-only challenge handles (2FA / WebAuthn)** — the cookie carries a random handle; the state lives in the cache and never touches the database.

**Single-use claim via conditional update** — how magic-link approval is made race-safe:

```php
$claimed = UserLoginLink::where('id', $id)->where('status', 'approved')
    ->update(['status' => 'consumed']);

if ($claimed === 0) { /* someone else won the race */ }
```

**Frontend page script**

```php
@push('scripts')
    <script src="{{ asset('assets/js/auth/login-link.js') }}"></script>
    <script>
        loginLink.init({ pollUrl: '{{ url('/api/auth/login-link/poll') }}' });
    </script>
@endpush
```

---

## Common Pitfalls

| Pitfall | Consequence | Avoid by |
|---|---|---|
| Importing `App\Models\User` instead of `App\Models\Auth\User` | Broken role checks, empty query results | Check the [Legacy vs. Current](#legacy-vs-current-critical) table |
| Moving/renaming a class without re-dumping the autoloader | Fatal "class not found" on a *deleted* path — the optimized classmap is authoritative | `php composer.phar dump-autoload` |
| Returning a non-200 HTTP status from an auth endpoint | Frontend callback never fires; user sees nothing | Use `Response`, keep 200 |
| Adding a column to `users` for auth state | Wrong table — auth state is normalised across `user_*` tables | Use the existing table for that concern |
| Assuming `php artisan test` is isolated | Runs against the **real** database | Configure a test DB in `phpunit.xml` first |
| Writing Tailwind classes | Silently unstyled — Tailwind is installed but inactive | Use Bootstrap 5 utilities |
| Editing `resources/js/app.js` expecting a browser change | That bundle is never loaded | Edit `public/assets/js/*.js` |
| Forgetting `parent::__construct()` in a controller | `$general` / settings missing → view errors | Always call it |
| Trusting `Auth::login()` / `Auth::logout()` | `SessionTokenGuard` implements only `Guard`, so these fatal | Use `SessionService::issue()` / `revoke()` |
| Adding a distinct error message on a failure path | Enables user enumeration | Keep messages generic; log the real reason |
| Editing Blade and not seeing the change | Compiled views are cached | `php artisan view:clear` |
| Changing settings and not seeing the change | Settings cached under key `setting` | `php artisan cache:clear` |

---

## AI Agent Instructions

**Before changing anything**

1. Grep for how the codebase already solves the problem; match that, not stock Laravel.
2. Confirm which namespace you need (legacy vs. current).
3. Read `docs/local/task_pending.md` — the thing you are about to "fix" may be a known, documented issue with a planned approach.

**While working**

- Change the minimum necessary. Do not opportunistically refactor unrelated code.
- Reuse `Response`, `SignedCookie`, `ClientInfo`, `ActivityService` — do not reimplement them.
- Match surrounding comment density and style.
- Add new auth routes to `routes/auth.php`, not `routes/web.php`.

**Before reporting done**

- Run `./vendor/bin/pint`.
- **Actually exercise the change** — start the server, hit the route, confirm the status code. This codebase has repeatedly failed in ways only a live request reveals (stale autoload, column type mismatches, missing tables).
- If you could not verify something, **say so explicitly.** Do not describe untested code as working.
- Report failures with the real output. Never claim a test passed that you did not run.

**Security-sensitive work**

Auth, sessions, cookies, hashing, rate limiting and 2FA are load-bearing. For changes there: read `docs/authentication.md` first, keep the [Project-Specific Rules](#project-specific-rules) intact, and flag any behaviour change explicitly rather than burying it.

---

## References

| Document | Contents |
|---|---|
| [`docs/architecture.md`](docs/architecture.md) | Request lifecycle, layer boundaries, guard resolution, caching, legacy/current split |
| [`docs/authentication.md`](docs/authentication.md) | Cookies, sessions, every auth flow, 2FA, passkeys, OAuth, middleware |
| [`docs/api.md`](docs/api.md) | Endpoint reference, envelope, validation, rate limits, error handling |
| [`docs/frontend.md`](docs/frontend.md) | Layouts, Blade conventions, JS patterns, asset pipeline, PJAX |
| `docs/local/task_pending.md` | Known issues and unfinished work (verified) |
| `docs/local/plan_improvemtns.md` | Refactoring backlog |
| `docs/local/plan_security.md` | Security findings and hardening plan |

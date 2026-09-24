# Authentication

> Endpoint paths in this document have no `/api` prefix (Laravel web-route style). The code still registers `api/auth/*` until Phase 0 of [`local/module_structure.md`](local/module_structure.md#phase-0-drop-the-api-prefix) lands.

Complete reference for the authentication system: cookies, sessions, guards, middleware, and every supported sign-in flow.

Summary in [`../AGENTS.md`](../AGENTS.md#authentication-overview).

> This system is security-critical and its behaviour is deliberate. Before changing anything here, read the [Project-Specific Rules](../AGENTS.md#project-specific-rules) and `docs/local/plan_security.md`.

---

## Model

There is **one `users` table** for both end users and administrators; `users.role` distinguishes them. There is no separate admin table and no separate admin guard.

Credentials are normalised away from `users`:

```
users                 identity, role, status, profile
  └─ user_accounts    one row per auth provider
       provider_id = 'credential'  → argon2id password hash
       provider_id = 'google'      → OAuth tokens, account_id = Google `sub`
```

`users` has **no** `password` column. A user can therefore have a Google account and no password, or both.

---

## Cookies

All auth cookies are `HttpOnly`, `Path=/`, `SameSite=Lax`, and `Secure` when `APP_ENV=production`. Names are prefixed with `config('setting.app_uid')` (falling back to `APP_UID`), e.g. `demo_session_token`.

| Cookie | Lifetime | Signed | Contents |
|---|---|---|---|
| `{uid}_session_token` | 30d (remember) / 1d | no | raw `base64url(random_bytes(32))` |
| `{uid}_device_uid` | 365d | no | 64-char random device identifier |
| `{uid}_tfa` | 600s | **yes** | 2FA challenge handle |
| `{uid}_wac` | 300s | **yes** | WebAuthn challenge handle |

The session token is unsigned because it is already an opaque 256-bit random value looked up server-side. Challenge handles are signed because they are presented back as claims.

### Signing scheme

```
value = payload . '.' . base64url(hmac_sha256(payload, ENCRYPTION_KEY))
```

Verified with `hash_equals()`. Implemented in `App\Helpers\SignedCookie`.

`ENCRYPTION_KEY` is a **separate ≥32-byte env var, not `APP_KEY`** — so cookie signing survives Laravel key rotation. `SignedCookie::key()` throws if it is missing or under 16 bytes.

```php
SignedCookie::queue('tfa', $handle, 600);        // signed
SignedCookie::queueRaw('session_token', $t, $s); // raw
SignedCookie::forget('tfa');
SignedCookie::verify($request->cookie(SignedCookie::name('tfa'))); // → payload|null
```

---

## Sessions

`user_sessions` rows, created by `SessionService::issue()`:

| Column | Notes |
|---|---|
| `token` | `base64url(random_bytes(32))`, `ascii_bin`, **unique** |
| `user_id` | FK → `users.id` |
| `expires_at` | now + 30d (remember) or +1d |
| `ip_address`, `user_agent`, `device_uid` | captured via `ClientInfo` |
| `remember` | drives the TTL on refresh |

**Validation** (`SessionService::validate()`): resolve token → check expiry → resolve user (cached) → maybe slide expiry. Any failure revokes the session and returns `null`.

**Sliding expiry:** once `updated_at` is older than `session_update_age` (24h), `expires_at` moves forward by the original lifetime. The token never rotates.

**Revocation:**

```php
$sessions->revoke($token);              // one session
$sessions->revokeAllForUser($userId);   // every session for a user
```

`revokeAllForUser()` runs on **password change and password reset** — which logs the user out of the browser they are currently using. This is intentional; surface it in the UI.

---

## The Guard

`App\Helpers\SessionTokenGuard`, registered as the `session_token` driver for the `web` guard. Details in [`architecture.md`](architecture.md#authentication-guard-resolution).

Key points:

- `auth()->user()` returns `App\Models\Auth\User` everywhere, including legacy controllers.
- Resolution is lazy and memoised per request.
- Only implements `Guard`, **not** `StatefulGuard` — `Auth::login()` / `Auth::logout()` / `Auth::attempt()` will fatal. Use `SessionService`.

---

## Middleware

| Alias | Class | Behaviour |
|---|---|---|
| `device.uid` | `EnsureDeviceUid` | Ensures a device UID exists; stashes it on the request *and* queues the cookie, so a login in the same request can record it |
| `auth.user` | `AuthenticateSession` | Requires a user. JSON → envelope error; HTML → `redirect('/login?redirect=…')`. Also gates every non-auth page in `routes/web.php` |
| `auth.admin` | `AuthenticateAdminSession` | Requires a user **+** `isAdmin()` **+** `hasPermission()`. Also gates the admin panel in `routes/web.php` |
| `auth.redirect` | `RedirectIfAuthenticated` | Bounces authenticated users off `/login`, `/register`; clears an invalid cookie |
| `admin.guest.redirect` | `RedirectIfAdminAuthenticated` | Admin equivalent → `/admin/dashboard` |
| `auth.throttle:{name}` | `AuthRateLimit` | Per-tier rate limiting, fails closed |

`auth.redirect` deliberately performs a real (cached) `validate()` rather than checking cookie presence — presence-only checks bounce a user with a stale cookie between `/login` and `/dashboard` forever.

### Rate limiting

Keyed `{name}:{path}:{ip}`; the poll tier keys on `{request_id}:{ip}` instead.

| Tier | Limit |
|---|---|
| `login`, `admin_login` | 10 / 900s |
| `register`, `otp`, `forgot_password`, `reset_password`, `verify_account`, `login_otp`, `tfa` | 5 / 900s |
| `login_link` | 5 / 300s |
| `login_link_poll` | 900 / 300s |
| `login_link_approve` | 20 / 300s |

Emits `Retry-After` and `X-RateLimit-*`. A cache failure returns "too many requests" rather than allowing the request through.

> Limits are per-IP only. There is no per-account throttle, so a distributed attack on one account is not rate-limited. See `docs/local/plan_security.md`.

---

## Flows

### Password login

`POST /auth/login`

1. `AuthService::authenticate()` — lowercase the email, load the user, load the `credential` account, `Hash::check()`.
2. Transparent rehash if `Hash::needsRehash()` (upgrades legacy bcrypt to argon2id).
3. Unverified email + `setting.user_email_verify` → send OTP, return `{next: 'verify-account'}`.
4. 2FA enabled and device not trusted → `TfaService::startLoginChallenge()`, return `{next: 'tfa'}`.
5. Otherwise issue the session and return `{next: 'dashboard'}`.

**Enumeration safety:** unknown email and wrong-role admin login both run `dummyPasswordCheck()` (an argon2id verify against a fixed hash with no known plaintext) so failures cost the same wall-clock time, and return the identical generic message.

> ⚠️ Known gap: the `isActive()` check runs *before* password verification and returns a distinct `"Account is disabled"` message, which leaks account existence to an unauthenticated caller. Documented as **H1** in `docs/local/plan_security.md`.

### Admin login

`POST /admin/auth/login` → same path with `requireAdmin: true`. A non-admin account gets `dummyPasswordCheck()` and the generic message — never "you are not an admin".

### Registration & email verification

`POST /auth/register` → creates `users` + `user_accounts(credential)` rows, logs `REGISTER`, and (when `setting.user_email_verify` is on) issues an OTP and returns `{next: 'verify-account'}`.

`POST /auth/verify-account` verifies the OTP, sets `email_verified`, and invalidates the user cache.

### OTP mechanics

`OtpService` backs verification, password reset and email-based 2FA.

- 6 digits via `random_int`, argon2id-hashed into `user_verifications.value`.
- `identifier = "{purpose}:{lowercased email}"`; prior rows for that identifier are deleted, so only one OTP is ever live per purpose+email.
- 600-second TTL.
- **Attempts increment atomically before the compare**, inside `DB::transaction()` with `lockForUpdate()`. Exceeding `otp_max_attempts` deletes the row. Success deletes it too (single use).

Preserve this ordering: incrementing after the compare would let an attacker guess indefinitely by never "using" an attempt.

### Forgot / reset password

`POST /auth/forgot-password` always returns the same generic message regardless of whether the email exists.
`POST /auth/reset-password` verifies the OTP, writes the new hash, then **revokes every session**.

---

## Two-Factor Authentication

Challenge state lives **only in the cache**, never the database. The signed `{uid}_tfa` cookie carries a random handle; `auth:tfa:{handle}` holds `{user_id, remember}`.

Three methods, registered in `TfaService` and implemented under `Services/Auth/Tfa/`:

| Method | Class | Verification |
|---|---|---|
| `totp` | `TotpMethod` | `google2fa` against the encrypted secret, window ±1 step |
| `otp` | `EmailOtpMethod` | Emailed 6-digit code via `OtpService` |
| `backup` | `BackupCodeMethod` | argon2id hash match, code spliced out on use |

### Login-time challenge

```
POST /auth/login          → {next: 'tfa'}, sets {uid}_tfa
GET  /auth/tfa/methods    → available methods for this handle
POST /auth/tfa/send-otp   → emails a code (method 'otp')
POST /auth/tfa/verify     → {method, code, trust_device} → session issued
```

Failures call `bumpTfaAttempts()`. Past `tfa_max_attempts` the challenge is consumed and the user restarts login.

### Setup and management

```
GET  /auth/2fa/status
POST /auth/2fa/enable              password → secret, otpauth URI, QR SVG, backup codes
POST /auth/2fa/verify-setup        TOTP code → verified = 1, two_factor_enabled = 1
POST /auth/2fa/disable
POST /auth/2fa/remove-authenticator  drops TOTP only, keeps email/backup
POST /auth/2fa/backup-codes        regenerate
```

The TOTP secret is stored with `Crypt::encryptString()`. Backup codes are 10 × `strtoupper(bin2hex(random_bytes(4)))`, stored as a JSON array of argon2id hashes.

QR codes are rendered as **inline SVG** by `bacon/bacon-qr-code` — no GD/Imagick dependency. Insert `data.qr_svg` as raw HTML.

### Trusted devices

Ticking "trust this device" upserts `user_devices` on `(user_id, device_uid)` with a 30-day expiry, and subsequent logins from that device skip the challenge. **Disabling 2FA revokes all trusted devices.**

---

## Magic Login Links

Sign in on device A by approving on device B (or the same device, from email).

```
POST /auth/login-link            → {request_id, poll_token, code, expires_at}
POST /auth/login-link/poll       → {state: pending|approved|rejected|expired}
GET  /auth/login-link/approve    → approval info for the emailed link
POST /auth/login-link/approve    → {action: approve|reject}
```

Mechanics:

- 300-second TTL. Poll and link tokens are each 32 random bytes; `code` is 6 plaintext digits shown on both devices so the user can confirm they match.
- Email link: `{APP_URL}/login/approve?id={rowId}&token={linkToken}`.
- **Unknown email fabricates a `request_id` *and* a `code`, writes nothing, and returns an identical response shape** — the response must not reveal whether the address exists.
- The client checks expiry **before** polling, so a dead request stops burning the poll budget.

**Single-use claim** — the reason approval cannot be replayed:

```php
$claimed = UserLoginLink::where('id', $id)->where('status', 'approved')
    ->update(['status' => 'consumed']);

if ($claimed === 0) {
    return ['state' => 'pending'];   // another poll won the race
}
```

Only the first poll to observe `approved` transitions it to `consumed` and receives a session.

Requires a `login-link` row in `email_templates` — seeded by `AuthSeeder`.

> ⚠️ Poll/link tokens are currently argon2id-hashed. They are 256-bit random values, so SHA-256 is both sufficient and appropriate; argon2id on a 2-second polling loop is a resource-exhaustion risk. See **H2** in `docs/local/plan_security.md`.

---

## Passkeys (WebAuthn)

Built on `web-auth/webauthn-lib` v5 using its stock ceremony managers — signature verification, origin checks and counter/replay detection are all delegated to the library. **Do not hand-roll any part of this.**

Configuration (`PasskeyService`):

- `rpId` = hostname of `APP_URL`
- `attestation: none`
- `residentKey: preferred`, `userVerification: preferred`
- Allowed origins pinned to `APP_URL`
- Algorithms: ES256 (-7), RS256 (-257)

```
POST /auth/passkey/register-options   (authenticated)
POST /auth/passkey/register-verify    (authenticated)
GET  /auth/passkey/list               (authenticated)
POST /auth/passkey/delete             (authenticated)
POST /auth/passkey/login-options      (public)
POST /auth/passkey/login-verify       (public)
```

Login options **omit `allowCredentials`** — credentials are discoverable, so the user signs in without typing an email.

Browser marshalling lives in `public/assets/js/auth/passkey.js`: the server speaks base64url, `navigator.credentials` speaks `ArrayBuffer`. `publicKeyCredentialToJSON()` handles both directions for registration and assertion.

`user_passkeys.counter` is updated after each successful assertion; the library's `ThrowExceptionIfInvalid` counter checker rejects replays.

> ⚠️ The browser ceremony has **never been executed** — no authenticator is available in the test environment. Server-side option generation is verified; the round trip is not. Treat passkey login as unverified until a manual browser pass confirms replay rejection and counter increment.

---

## Google OAuth

Built on `laravel/socialite`, **not** a hand-rolled OIDC flow. Socialite exchanges the auth code server-to-server and reads the userinfo endpoint; it never consumes an `id_token`, so there is no JWT to verify and no PKCE/nonce step to implement.

```
GET /auth/google            → redirect to Google
GET /auth/google/callback   → link or create, then issue a session
```

Linking precedence:

1. `(provider_id = 'google', account_id = sub)` → sign in.
2. Else match by email → link a `google` row to the existing user.
3. Else create the user with `email_verified = true`.

Accounts whose Google email is unverified are rejected. Inactive accounts are rejected.

Socialite's `state` parameter provides CSRF protection — the routes are inside the `web` group so the session is available. **Do not add `->stateless()`**; it would disable that check for no benefit.

Configure `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_REDIRECT_URI`. When blank, the sign-in buttons are hidden.

> Untested — no credentials are configured in this environment.

---

## Activity Logging

Every security-relevant action writes a `user_activities` row via `ActivityService::log()`, capturing device UID, IP, user agent and an `App\Constants\UserActivity` **key** (never a display label).

```php
$this->activity->log($request, $user->id, UserActivity::LOGIN_SUCCESS);
```

Common keys: `LOGIN_SUCCESS`, `LOGIN_FAILED`, `LOGOUT`, `REGISTER`, `PASSWORD_CHANGED`, `TFA_ENABLED`, `TFA_DISABLED`, `PASSKEY_ADDED`, `PASSKEY_DELETED`, `LOGIN_WITH_LINK`, `LOGIN_WITH_SOCIAL`, `BACKUP_CODES_REGENERATED`.

Add a constant to `App\Constants\UserActivity` (with a label in `LABELS`) before logging a new type.

---

## Security Invariants

Do not regress these without a deliberate, stated decision:

1. Passwords, OTPs and backup codes → **argon2id**. High-entropy random tokens → **SHA-256**.
2. Attempt counters increment **before** comparison.
3. Auth failures are **generic and timing-equalised**.
4. Token/UUID columns keep **`ascii_bin`** collation.
5. Cookies are signed with **`ENCRYPTION_KEY`**, compared with `hash_equals`.
6. Challenge state stays **cache-only**.
7. Password change/reset **revokes all sessions**.
8. Every public auth endpoint is **rate-limited and CSRF-protected**.

Open findings and the hardening plan: `docs/local/plan_security.md`.

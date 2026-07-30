# API

Reference for the JSON endpoints: response envelope, validation, error handling, rate limiting, and the full endpoint list.

Summary in [`../AGENTS.md`](../AGENTS.md#api-overview).

---

## Response Envelope

Every JSON endpoint returns the same three-key object:

```json
{
  "status": 1,
  "message": "Logged in successfully",
  "data": { "next": "dashboard" }
}
```

| Field | Type | Meaning |
|---|---|---|
| `status` | `1` \| `0` | Success or failure. **This is the result, not the HTTP code.** |
| `message` | `string` \| `null` | Human-readable, safe to display |
| `data` | `object` | Payload; always an object, never `null` |

### HTTP status is always 200 — deliberately

Failures return **HTTP 200** with `status: 0`.

The reason is in `public/assets/js/app.js`: `ajaxRequest()` uses `$.ajax`, whose `success` callback — and therefore the caller's callback from `app.ajaxForm` / `app.ajaxPost` — only fires on a 2xx response. Anything else is routed to a generic bodyless `error` handler that discards `response.data`.

Page scripts need `data` on the **failure** path (`data.next === 'verify-account'`, `data.next === 'tfa'`), so the envelope must reach `success`. The `status` field carries the outcome instead.

Two exceptions: validation failures and CSRF failures are raised by the framework and return **422** and **419**.

> This trade-off is understood and revisitable — moving to honest status codes requires a global `error` handler in `app.js` that parses the envelope out of non-2xx responses. Tracked in `docs/local/plan_improvemtns.md`.

### Building responses

```php
use App\Helpers\ApiResult;

ApiResult::success('Password changed successfully')->toResponse();
ApiResult::success(null, ['next' => 'tfa'])->toResponse();
ApiResult::failure('Invalid email or password')->toResponse();
ApiResult::failure('Please verify your account', [
    'next'  => 'verify-account',
    'email' => $user->email,
])->toResponse();
```

Mapping a service result:

```php
return ($result['ok']
    ? ApiResult::success($result['message'])
    : ApiResult::failure($result['message'])
)->toResponse();
```

Attaching cookies:

```php
return ApiResult::success('Logged in')->withCookies([$cookie]);
```

---

## The `next` Convention

`data.next` tells the frontend where to go, so redirect logic lives server-side:

| Value | Frontend action |
|---|---|
| `dashboard` | Redirect to `/dashboard` |
| `admin-dashboard` | Redirect to `/admin/dashboard` |
| `tfa` | Redirect to `/verify?type=tfa` |
| `verify-account` | Redirect to `/verify-account?code={btoa(email)}` |
| `refresh` | Reload the current page |

```js
app.ajaxForm(this, function (response) {
    if (response.status == 1) {
        if (response.data.next === 'tfa') { window.location.href = '/verify?type=tfa'; return; }
        window.location.href = '/dashboard';
    } else {
        if (response.data.next === 'verify-account') { /* … */ return; }
        app.showMessage(response.message, 'error');
    }
});
```

---

## Validation

FormRequests in `App\Http\Requests\Auth\`, overriding `failedValidation()` so errors use the envelope instead of Laravel's default 422 body:

```php
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'       => ['required', 'string', 'regex:/^[a-zA-Z ]+$/', 'min:3', 'max:50'],
            'email'            => ['required', 'email', 'max:255'],
            'phone'            => ['required', 'digits:10'],
            'password'         => ['required', 'string', 'min:6', 'max:100'],
            'confirm_password' => ['required', 'same:password'],
            'agree'            => ['accepted'],
        ];
    }

    protected function failedValidation(ValidatorContract $validator)
    {
        throw new HttpResponseException(
            ApiResult::failure($validator->errors()->first())->toResponse()
        );
    }
}
```

**Only the first error is returned**, as a plain string — the UI shows one message at a time rather than a field-keyed error bag.

Simple endpoints may validate inline instead:

```php
$request->validate([
    'id'     => ['required', 'string'],
    'action' => ['required', 'in:approve,reject'],
]);
```

---

## Error Handling

| Condition | HTTP | Body |
|---|---|---|
| Success | 200 | `{status: 1, …}` |
| Business failure (bad credentials, expired OTP) | 200 | `{status: 0, message, data}` |
| Rate limited | 200 | `{status: 0, message: "Too many requests…"}` + `Retry-After` |
| Unauthenticated (JSON request) | 200 | `{status: 0, message: "Authentication required"}` |
| Validation failure | 422 | `{status: 0, message: "<first error>"}` |
| CSRF token missing/stale | 419 | Laravel's page-expired response |

Messages must be **safe to display and non-revealing**. Never leak whether an email exists, whether an account is an admin, or which specific credential was wrong.

```php
ApiResult::failure('Invalid email or password')   // ✅
ApiResult::failure('No account with that email')  // ❌ enumeration
```

---

## Rate Limiting

Apply `auth.throttle:{tier}` to every public auth endpoint:

```php
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('auth.throttle:login');
```

Tiers are defined in `AuthRateLimit::LIMITS` — add one there rather than inventing a per-route limit. See [`authentication.md`](authentication.md#rate-limiting) for the table.

Responses carry `Retry-After`, `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`. The limiter **fails closed**: if the cache store errors, requests are rejected.

---

## CSRF

All auth routes live in the `web` middleware group, so **CSRF applies to every POST**. Verified: all eleven public auth POST endpoints return 419 without a token.

The token reaches JS via a global defined in the layouts:

```blade
var CSRF_NAME  = '_token';
var CSRF_TOKEN = "{{ Session::token() }}";
```

`app.ajaxPost` / `app.ajaxForm` attach it automatically. Both layouts also expose `<meta name="csrf-token">`.

If you add a POST endpoint that a non-browser client must call, do **not** exempt it from CSRF without a deliberate decision — prefer a token.

---

## Endpoint Reference

### Public — authentication

| Method | Path | Throttle | Purpose |
|---|---|---|---|
| POST | `/api/auth/login` | `login` | Password login |
| POST | `/api/auth/register` | `register` | Create an account |
| POST | `/api/auth/logout` | — | End the session |
| GET | `/api/auth/session` | — | Current user, or `status: 0` |
| POST | `/api/auth/forgot-password` | `forgot_password` | Send reset OTP |
| POST | `/api/auth/reset-password` | `reset_password` | Reset with OTP |
| POST | `/api/auth/verify-account` | `verify_account` | Verify email with OTP |
| POST | `/api/auth/otp` | `otp` | Resend verification OTP |
| POST | `/api/admin/auth/login` | `admin_login` | Admin login (`requireAdmin`) |

### Public — 2FA challenge (gated by the signed `tfa` cookie, not a session)

| Method | Path | Throttle |
|---|---|---|
| GET | `/api/auth/tfa/methods` | `tfa` |
| POST | `/api/auth/tfa/send-otp` | `tfa` |
| POST | `/api/auth/tfa/verify` | `tfa` |

### Public — magic login link

| Method | Path | Throttle |
|---|---|---|
| POST | `/api/auth/login-link` | `login_link` |
| POST | `/api/auth/login-link/poll` | `login_link_poll` |
| GET | `/api/auth/login-link/approve` | `login_link_approve` |
| POST | `/api/auth/login-link/approve` | `login_link_approve` |

### Public — passkeys & OAuth

| Method | Path | Notes |
|---|---|---|
| POST | `/api/auth/passkey/login-options` | Discoverable credentials |
| POST | `/api/auth/passkey/login-verify` | Issues a session |
| GET | `/api/auth/google` | Redirect to Google |
| GET | `/api/auth/google/callback` | Link/create, then session |

### Authenticated (`auth.user`)

| Method | Path | Purpose |
|---|---|---|
| POST | `/api/auth/change-password` | Change password (revokes all sessions) |
| GET | `/api/auth/2fa/status` | 2FA state + backup codes remaining |
| POST | `/api/auth/2fa/enable` | Begin setup → secret, QR, backup codes |
| POST | `/api/auth/2fa/verify-setup` | Confirm TOTP, activate |
| POST | `/api/auth/2fa/disable` | Disable 2FA, revoke trusted devices |
| POST | `/api/auth/2fa/remove-authenticator` | Remove TOTP only |
| POST | `/api/auth/2fa/backup-codes` | Regenerate backup codes |
| GET | `/api/auth/passkey/list` | List passkeys |
| POST | `/api/auth/passkey/register-options` | Begin registration |
| POST | `/api/auth/passkey/register-verify` | Complete registration |
| POST | `/api/auth/passkey/delete` | Delete a passkey |

Regenerate this list with:

```bash
php artisan route:list --path=api
```

---

## Adding an Endpoint

1. **Route** in `routes/auth.php`, in the matching group, with a throttle tier.
2. **FormRequest** if it takes more than one or two fields.
3. **Service method** returning `['ok' => bool, 'message' => …]`.
4. **Controller** maps that to `ApiResult`.
5. **Log** via `ActivityService` if security-relevant.
6. **Test it live** — confirm the status code and the envelope.

```php
// routes/auth.php
Route::post('/set-password', [PasswordController::class, 'setPassword'])
    ->middleware('auth.throttle:reset_password');

// Controller
public function setPassword(SetPasswordRequest $request)
{
    $result = $this->auth->setPassword($request, $request->user(), $request->input('password'));

    return ($result['ok']
        ? ApiResult::success($result['message'])
        : ApiResult::failure($result['message'])
    )->toResponse();
}
```

Endpoints that were planned but never wired (including `set-password`, whose service method already exists) are listed in `docs/local/task_pending.md`.

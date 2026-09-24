# HTTP Endpoints

Reference for the AJAX/JSON endpoints: response envelope, validation, error handling, rate limiting, and the full endpoint list.

> **URL convention: no `/api` prefix.** This is a server-rendered Laravel app, so every endpoint is an ordinary `web`-group route (session, CSRF, `device.uid`). JSON endpoints sit next to the pages they serve, grouped by module, e.g. `POST /auth/login` beside `GET /login`. There is no separate API surface, API guard or token auth.
>
> Routes are registered without the prefix (done in Phase 0 of [`local/module_structure.md`](local/module_structure.md#phase-0-drop-the-api-prefix)).

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

### HTTP status

`Response` mirrors the Next app's `src/server/utils/response.ts`: same function names, same body. The body is frozen to `status` / `message` / `data`; anything extra goes inside `data`, never on the top level. `data` defaults to `[]`.

Which HTTP code to use depends on what the caller needs, because of `public/assets/js/app.js`: `ajaxRequest()` uses `$.ajax`, whose `success` callback (and therefore the callback passed to `app.ajaxForm` / `app.ajaxPost`) only fires on a 2xx response. Any other status goes to `ajaxError`, which shows `responseJSON.message` and discards `data`.

| Situation | HTTP | Body |
|---|---|---|
| Success | 200 | `status: 1` |
| Failure the page handles itself (needs `data`, e.g. `data.next === 'verify-account'`, or a wrong password shown inline) | **200** | `status: 0` |
| Failure that only needs a message shown | 4xx/5xx (`sendError`) | `status: 0`, `data: []` |
| Validation failure (FormRequest or inline `$request->validate()`) | 422 | `status: 0`, first error in `message` |
| Not signed in / not allowed | 401 | `status: 0` |
| CSRF token expired | 419 | `status: 0` |
| Rate limited | 429 | `status: 0` |

Validation, CSRF and rate-limit failures are rendered as the envelope for AJAX requests (`bootstrap/app.php` exception handlers and `AuthRateLimit`); non-AJAX browser requests still get Laravel's normal pages.

### Building responses

```php
use App\Helpers\Response;

Response::sendMessage('Password changed successfully');          // status 1, data []
Response::sendMessage('Invalid email or password', 0);           // status 0, HTTP 200
Response::sendError(401, 'Authentication required');             // status 0, HTTP 401
Response::sendData(['requires_tfa' => true]);                    // status 1 with a payload
Response::sendResponse(200, [                                    // full control
    'status'  => 0,
    'message' => 'Please verify your account',
    'data'    => ['requires_verification' => true, 'email' => $user->email],
]);
```

Mapping a service result with `sendResult()`. It accepts either shape:

```php
// Next style: ['status' => 1|0, 'message' => ..., 'data' => [...], 'http_status' => 200]
// This app's service style: ['ok' => bool, 'message' => ..., ...extra]
//   ok    -> status (1/0)
//   extra -> merged into data
return Response::sendResult($result);
```

Extra headers: `Response::sendResultWithHeaders($result, ['X-Foo' => 'bar'])`. Cookies are queued instead (`SignedCookie::queueRaw()`) and attached by the framework.

---

---

## Follow-up actions live in the view

The response never says where to go. The view declares what happens after a successful request with `data-next` and `data-next-url` on the **form or button that triggers it**, and `app.js` runs it (after showing the message):

| `data-next` | Action |
|---|---|
| `load` | PJAX-load `data-next-url` |
| `refresh` | Reload the current page through PJAX |
| `table_refresh` | Reload the DataTable (`datatableObj`) |
| `reload` | Full page reload |
| `redirect` | `window.location = data-next-url` |
| `hide_modal` / `show_modal_view` | Close the common modal / load `data-next-url` into it |

Several actions can be combined: `data-next="hide_modal,table_refresh"`.

```blade
{{-- after saving, go back to the list --}}
<form action="{{ route('admin/user/save') }}" data-next="load" data-next-url="{{ route('admin/user') }}">

{{-- after deleting a row, reload the table --}}
<button onclick="app.confirmAction(this);" data-action="{{ route('admin/user/delete') }}"
        data-id="{{ $id }}" data-next="table_refresh">Delete</button>
```

Pages that need to branch on the outcome (login, register, 2FA) pass their own callback and read **data flags**, which are data, not navigation:

```js
app.ajaxForm(this, function (response) {
    if (response.status == 1) {
        window.location.href = response.data.requires_tfa ? '/verify?type=tfa' : '/dashboard';
    } else if (response.data.requires_verification) {
        window.location.href = '/verify-account?code=' + btoa(response.data.email);
    } else {
        app.showMessage(response.message, 'error');
    }
});
```

DataTables endpoints return DataTables' own JSON (`recordsTotal`, `recordsFiltered`, `draw`, `data`), not the envelope.

---

## Validation

FormRequests live in the module (`app/Modules/<Module>/Requests/`). They do **not** override `failedValidation()`: for AJAX requests `bootstrap/app.php` renders any `ValidationException` — including inline `$request->validate()` — as the envelope with HTTP 422 and the **first error** as a plain string. The UI shows one message at a time rather than a field-keyed error bag.

```php
class SaveNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'    => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'note'  => ['nullable', 'string', 'max:5000'],
        ];
    }
}
```

Simple endpoints may validate inline: `$request->validate(['id' => ['required', 'string']]);`.

---

## Error Handling

| Condition | HTTP | Body |
|---|---|---|
| Success | 200 | `{status: 1, …}` |
| Business failure (bad credentials, expired OTP) | 200 | `{status: 0, message, data}` |
| Rate limited | 429 | `{status: 0, message: "Too many requests…"}` + `Retry-After` |
| Unauthenticated (JSON request) | 401 | `{status: 0, message: "Authentication required"}` |
| Validation failure | 422 | `{status: 0, message: "<first error>"}` |
| CSRF token missing/stale | 419 | `{status: 0, message: "Your session has expired…"}` |

Messages must be **safe to display and non-revealing**. Never leak whether an email exists, whether an account is an admin, or which specific credential was wrong.

```php
Response::sendMessage('Invalid email or password', 0)      // ✅
Response::sendMessage('No account with that email', 0)     // ❌ enumeration
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
| POST | `/auth/login` | `login` | Password login |
| POST | `/auth/register` | `register` | Create an account |
| POST | `/auth/logout` | — | End the session |
| GET | `/auth/session` | — | Current user, or `status: 0` |
| POST | `/auth/forgot-password` | `forgot_password` | Send reset OTP |
| POST | `/auth/reset-password` | `reset_password` | Reset with OTP |
| POST | `/auth/verify-account` | `verify_account` | Verify email with OTP |
| POST | `/auth/otp` | `otp` | Resend verification OTP |
| POST | `/admin/auth/login` | `admin_login` | Admin login (`requireAdmin`) |

### Public — 2FA challenge (gated by the signed `tfa` cookie, not a session)

| Method | Path | Throttle |
|---|---|---|
| GET | `/auth/tfa/methods` | `tfa` |
| POST | `/auth/tfa/send-otp` | `tfa` |
| POST | `/auth/tfa/verify` | `tfa` |

### Public — magic login link

| Method | Path | Throttle |
|---|---|---|
| POST | `/auth/login-link` | `login_link` |
| POST | `/auth/login-link/poll` | `login_link_poll` |
| GET | `/auth/login-link/approve` | `login_link_approve` |
| POST | `/auth/login-link/approve` | `login_link_approve` |

### Public — passkeys & OAuth

| Method | Path | Notes |
|---|---|---|
| POST | `/auth/passkey/login-options` | Discoverable credentials |
| POST | `/auth/passkey/login-verify` | Issues a session |
| GET | `/auth/google` | Redirect to Google |
| GET | `/auth/google/callback` | Link/create, then session |

### Authenticated (`auth.user`)

| Method | Path | Purpose |
|---|---|---|
| POST | `/auth/change-password` | Change password (revokes all sessions) |
| GET | `/auth/2fa/status` | 2FA state + backup codes remaining |
| POST | `/auth/2fa/enable` | Begin setup → secret, QR, backup codes |
| POST | `/auth/2fa/verify-setup` | Confirm TOTP, activate |
| POST | `/auth/2fa/disable` | Disable 2FA, revoke trusted devices |
| POST | `/auth/2fa/remove-authenticator` | Remove TOTP only |
| POST | `/auth/2fa/backup-codes` | Regenerate backup codes |
| GET | `/auth/passkey/list` | List passkeys |
| POST | `/auth/passkey/register-options` | Begin registration |
| POST | `/auth/passkey/register-verify` | Complete registration |
| POST | `/auth/passkey/delete` | Delete a passkey |

Regenerate this list with:

```bash
php artisan route:list --path=auth
```

---

## Adding an Endpoint

1. **Route** in the module's `<module>_routes.php` (admin modules: relative to the admin group), with a throttle for public endpoints.
2. **FormRequest** in the module's `Requests/`.
3. **Service method** returning `['ok' => bool, 'message' => …]`, calling repositories.
4. **Controller** maps that to `Response::sendResult()`.
5. **View**: put `data-next` / `data-next-url` on the form or button.
6. **Log** via `ActivityService` if security-relevant.
7. **Test it live** — confirm the status code and the envelope.

```php
// app/Modules/Auth/auth_routes.php
Route::middleware(['device.uid', 'auth.user'])->prefix('auth')->group(function () {
    Route::post('/set-password', [PasswordController::class, 'setPassword']);
});

// Controller
public function setPassword(SetPasswordRequest $request)
{
    $result = $this->auth->setPassword($request, $request->user(), $request->input('password'));

    return Response::sendResult($result);
}
```

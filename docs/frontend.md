# Frontend

Reference for Blade layouts, the asset pipeline, JavaScript conventions and PJAX navigation.

Summary in [`../AGENTS.md`](../AGENTS.md#frontend-overview).

---

## Stack

Server-rendered Blade + Bootstrap 5, with a vendored theme in `public/theme/`. **No SPA framework. No build step for application JavaScript.**

| Concern | Choice |
|---|---|
| Templating | Blade |
| CSS framework | Bootstrap 5 (vendored theme) |
| Application JS | Hand-written vanilla JS in `public/assets/js/` |
| jQuery | Present — Bootstrap's JS, `jquery-validate`, and the `app.*` AJAX helpers depend on it |
| Bundler | Vite — **CSS only**, and only for the main layout |

### What Vite does and does not do

`vite.config.js` declares two inputs, but only one is ever loaded by a view:

```js
input: ['resources/css/app.css', 'resources/js/app.js']
```

- `resources/css/app.css` → loaded by `layouts/main.blade.php` via `@vite([...])`. Editing it requires `npm run dev` or `npm run build`.
- `resources/js/app.js` → **built but never loaded by any Blade view.** Editing it changes nothing in the browser.

Browser JavaScript comes from `public/assets/js/*.js` via plain `<script src="…">` tags. To change page behaviour, edit those files — no build required.

### Tailwind is installed but inactive

`tailwindcss` and `@tailwindcss/vite` are in `package.json`, but:

- `vite.config.js` does not register the Tailwind plugin.
- No CSS file imports Tailwind (`resources/css/app.css` is just `@import './common.css';`).

**Tailwind classes will not render.** Use Bootstrap 5 utilities. The packages are leftovers from the Laravel skeleton and can be removed.

---

## Layouts

| Layout | Used by | Notes |
|---|---|---|
| `layouts/blank.blade.php` | Auth pages | Standalone card; no sidebar/navbar; no `@vite` |
| `layouts/main.blade.php` | Authenticated app | Full shell; PJAX-aware; loads `@vite(['resources/css/app.css'])` |
| `admin/layouts/blank.blade.php` | Admin auth pages | Admin equivalent |
| `admin/layouts/main.blade.php` | Admin panel | Admin equivalent |

Choosing:

```blade
@extends('layouts.blank')         {{-- login, register, verify, reset --}}
@extends('layouts.main')          {{-- dashboard, account pages --}}
@extends('admin.layouts.blank')   {{-- admin login --}}
@extends('admin.layouts.main')    {{-- admin CRUD --}}
```

Both `blank` layouts already load jQuery, Bootstrap, `jquery-validate`, `assets/js/common.js` and `assets/js/app.js`, and define the CSRF globals — an auth page needs no boilerplate beyond its own script.

### Controller requirement

Every layout references `$general` and `config('setting.*')`, both provided by the base controller constructor. **Always call `parent::__construct()`** or views fail with undefined-variable errors.

---

## Auth Page Markup

Follow the established structure so pages stay visually consistent:

```blade
@extends('layouts.blank')
@section('title', 'Login To Your Account')

@section('content')
<div class="container-xxl">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6">
      <div class="card px-sm-6 px-0">
        <div class="card-body">

          <div class="app-brand justify-content-center">
            <a href="{{ url('/') }}" class="app-brand-link d-flex align-items-center">
              <span class="app-brand-logo demo">
                <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                     class="brand-image img-circle elevation-3" style="height: 50px;">
              </span>
              <span class="app-brand-text demo text-heading fw-bold">
                {{ config('setting.app_name') }}
              </span>
            </a>
          </div>

          <h4 class="mb-1">Welcome 👋</h4>
          <p class="mb-6">Please log in to your account</p>

          <form id="login-form" action="{{ url('/api/auth/login') }}" method="POST">
            @csrf
            <div class="mb-6">
              <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="email" name="email" autofocus />
            </div>

            <div class="mb-6 form-password-toggle">
              <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
              <div class="input-group input-group-merge has-validation">
                <input type="password" id="password" class="form-control" name="password" />
                <span class="input-group-text cursor-pointer" data-password-toggle="#password">
                  <i class="icon-base bx bx-hide"></i>
                </span>
              </div>
            </div>

            <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
          </form>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
```

Reusable partials live in `resources/views/common/` (`message_alert`, `recaptcha`, `verify_otp_modal`, `totp_modal`). Check there before writing new markup.

---

## The `app.*` JavaScript Helpers

Defined in `public/assets/js/app.js` and available on every page. They already speak the response envelope, attach CSRF, and show/hide the loading indicator.

```js
app.ajaxPost(url, postData, cb)   // cb receives the parsed envelope
app.ajaxGet(url, cb)              // cb defaults to app.ajaxSuccess
app.ajaxForm(form, cb)            // serialises the form, then posts
app.showMessage(message, type)    // 'success' | 'error'
app.showLoading() / app.hideLoading()
app.setCookie(name, value, days) / app.getCookie(name)
```

Callbacks receive the **full envelope**, so branch on `response.status` yourself when the page needs `data` on the failure path:

```js
app.ajaxForm(this, function (response) {
    if (response.status == 1) {
        window.location.href = '/dashboard';
    } else {
        app.showMessage(response.message, 'error');
    }
});
```

Omitting `cb` falls back to the generic `ajaxSuccess`, which handles the common message/redirect cases.

---

## Writing New Page JavaScript

Vanilla only. **Do not add a framework, and do not introduce a new jQuery plugin.**

### Pattern A — self-initialising

For behaviour attached to elements that exist on load:

```js
/**
 * Magic login-link panel on the login page.
 */
(function () {
    'use strict';

    var toggleBtn = document.getElementById('magic-link-toggle');
    if (!toggleBtn) { return; }          // not on this page - bail

    var startUrl = toggleBtn.dataset.startUrl;   // URLs come from Blade

    toggleBtn.addEventListener('click', function () {
        app.ajaxPost(startUrl, { email: '…' }, function (response) {
            if (response.status != 1) {
                app.showMessage(response.message, 'error');
                return;
            }
            // …
        });
    });
})();
```

### Pattern B — explicit `init()`

When the page must pass configuration:

```js
var loginApprove = (function () {
    'use strict';

    function init(opts) {
        if (!opts.id || !opts.token) { /* … */ return; }
        app.ajaxGet(opts.infoUrl + '?id=' + encodeURIComponent(opts.id), function (response) { /* … */ });
    }

    return { init: init };
})();
```

```blade
@push('scripts')
    <script src="{{ asset('assets/js/auth/approve.js') }}"></script>
    <script>
        loginApprove.init({
            infoUrl: '{{ url('/api/auth/login-link/approve') }}',
            id:      '{{ request('id') }}',
            token:   '{{ request('token') }}',
        });
    </script>
@endpush
```

### Rules

1. **Never hardcode a URL in a `.js` file.** Pass it via `data-*` or `init()` — the app may be served from a subpath.
2. **Always guard on the target element** so a shared script is harmless on other pages.
3. `'use strict'` inside the IIFE.
4. Expose at most one named global, and only for Pattern B.
5. Use `app.*` helpers rather than raw `fetch`/`$.ajax` — you would otherwise reimplement CSRF, loading state and envelope parsing.
6. Clean up timers: clear any `setInterval` on success, failure **and** expiry.

### Current modules

| File | Purpose |
|---|---|
| `auth/password-toggle.js` | Show/hide password eye icon |
| `auth/countdown.js` | 60s resend countdown |
| `auth/login-link.js` | Magic-link panel + 2s polling |
| `auth/approve.js` | Approve/reject on `/login/approve` |
| `auth/passkey.js` | WebAuthn ceremonies + base64url marshalling |
| `auth/tfa-verify.js` | 2FA method picker and code entry |
| `account/tfa.js` | 2FA setup, QR, backup codes |
| `account/passkeys.js` | Passkey list/add/delete |

---

## WebAuthn Marshalling

`auth/passkey.js` bridges the encoding gap: the server speaks base64url, `navigator.credentials` speaks `ArrayBuffer`.

```js
passkeyAuth.isSupported()                                  // feature detect
passkeyAuth.register(optionsUrl, verifyUrl, name, done)    // create()
passkeyAuth.login(optionsUrl, verifyUrl, done)             // get()
```

Internally: `base64urlToBuffer()` / `bufferToBase64url()` convert in both directions, `optionsFromServer()` rewrites `challenge`, `user.id`, `excludeCredentials[].id` and `allowCredentials[].id`, and `publicKeyCredentialToJSON()` re-encodes the credential for POSTing — branching on `attestationObject` (registration) vs `authenticatorData` (assertion).

Always feature-detect and hide the entry point when unsupported:

```js
if (!passkeyAuth.isSupported()) { btn.style.display = 'none'; return; }
```

---

## CSRF in the Browser

Both layouts define:

```blade
var CSRF_NAME  = '_token';
var CSRF_TOKEN = "{{ Session::token() }}";
```

and expose `<meta name="csrf-token" content="{{ csrf_token() }}">`.

`app.ajaxPost` / `ajaxForm` attach the token automatically. Include `@csrf` in every `<form>` as well, since `ajaxForm` serialises the form.

A **419** response means a stale token — usually a cached page or an expired session. Reload rather than exempting the route.

---

## PJAX Navigation

`layouts/main.blade.php` supports partial navigation via `assets/js/pjax.js`. Links tagged `class="pjax"` fetch with `?partial=1`, and the layout returns only the `#main-content` fragment. `data-title` on that element updates the document title.

```blade
<a href="account/update" class="pjax">My Account</a>
```

Notes:

- Auth pages use `layouts/blank`, which has **no** PJAX branch — full page loads, no special handling.
- Scripts pushed via `@push('scripts')` inside `#main-content` re-execute on PJAX navigation. Make initialisation **idempotent** — guard against double-binding listeners.
- If a page must fully reload, omit the `pjax` class.

---

## Debugging

| Symptom | Likely cause |
|---|---|
| Blade edit has no effect | Compiled views cached → `php artisan view:clear` |
| CSS edit has no effect | Vite not running → `npm run dev`, or you edited the wrong file |
| JS edit has no effect | You edited `resources/js/app.js` (never loaded) instead of `public/assets/js/…` |
| Tailwind class does nothing | Tailwind is inactive — use Bootstrap |
| Button silently does nothing | Missing script tag, or the module bailed on a missing element — check the console |
| Callback never fires | Endpoint returned a non-2xx status; the envelope must be HTTP 200 (see [`api.md`](api.md#http-status-is-always-200--deliberately)) |
| `$general` undefined in a view | Controller did not call `parent::__construct()` |
| Settings change not reflected | Settings cached → `php artisan cache:clear` |

# AGENTS.md

Working guide for AI coding agents on this repository. Read this before making changes.

---

## Project Overview

A Laravel 12 application with a **public site** (home, blog, pages, contact), a **user area** (dashboard, notes, account) and a **Bootstrap-free Tailwind admin panel**. Its defining feature is a hand-rolled authentication system: password login, email-OTP verification, TOTP/email/backup-code 2FA with trusted devices, magic login links with second-device approval, WebAuthn passkeys and Google OAuth.

It is a port of a Next.js app (`/www/wwwroot/demo/next/next`). **The database is identical to Next's** (same tables, columns, defaults, enums, indexes, foreign keys — see [Database](#database)), and new pages reuse Next's Tailwind/shadcn class vocabulary.

The code is organised into **modules** (`app/Modules/*`). There is no legacy stack any more: the old integer-role `App\Models\User`, `UserAuth`, and the legacy Auth/Tfa/Account services were deleted. Do not reintroduce them.

---

## Tech Stack

| Layer | Choice |
|---|---|
| Framework | Laravel 12, PHP 8.2+ (running 8.4) |
| Database | MariaDB / MySQL (`DB_CONNECTION=mysql`) |
| Session & Cache | `database` driver (both) |
| Views | Blade + Tailwind CSS v4 (built by Vite); shadcn-style Blade components in `resources/views/components/ui` |
| Tables | jQuery DataTables 2 with the Tailwind styling integration (`dataTables.tailwindcss`) |
| Browser JS | Vanilla JS + jQuery `app.*` helpers in `public/assets/js/` (not bundled by Vite) |
| Password hashing | argon2id (m=65536, t=3, p=4) |
| 2FA | `pragmarx/google2fa` + `bacon/bacon-qr-code` |
| Passkeys | `web-auth/webauthn-lib` v5 |
| OAuth | `laravel/socialite` (Google) |
| Formatting | Laravel Pint |
| Tests | PHPUnit 11 |
| Response headers | `SecurityHeaders` middleware: CSP (script-src keeps `'unsafe-inline'`/`'unsafe-eval'` for Alpine, PJAX and inline handlers), X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy |

---

## Repository Structure

```
app/
  Modules/
    Admin/                     every admin-panel module
      Controllers/Controller.php   admin base controller
      Services/                    services shared by admin modules
      Auth/  Dashboard/  Account/  User/  Admins/  Activity/  Device/
      Page/  Seo/  Setting/  EmailTemplate/  Blog/
    Auth/  User/  Page/  Contact/  Site/  Blog/  Note/    end-user modules
      (each: Controllers/  Requests/  Services/  <module>_routes.php)
  Models/                      schema, casts, relationships only
    Auth/                      User + 8 auth tables (UUID PKs)
  Repositories/                every database query
    Auth/                      one repository per auth table
  Services/                    cross-module services (ActivityService, PermissionService, EmailTemplateService)
  Constants/  Helpers/  Http/Middleware/  Jobs/
resources/views/
  modules/<module>/            module views  (modules/admin/<module>/ for the admin panel)
  components/ui/               x-ui.* Blade components (Next's shadcn class strings)
  layouts/  common/  email/    shared
public/assets/js/              app.js, common.js, pjax.js, auth/*, account/*   (NOT built by Vite)
docs/                          documentation; docs/local/ is gitignored working notes
```

Module layout rules and the migration history are in [`docs/architecture.md`](docs/architecture.md).

---

## Architecture Summary

```
Request
  → web middleware (+ EnsureDeviceUid)
  → route middleware (auth.user / auth.admin / auth.throttle / auth.redirect / throttle)
  → Controller   thin: FormRequest validates → call a service → return Response / view
  → Service      business logic; talks to repositories, never to Eloquent directly
  → Repository   the only place that queries (Model::where, DB::table, Pagination)
  → Model        schema, casts, relationships
```

- **Routes** live in `app/Modules/<Module>/<module>_routes.php`, loaded by `bootstrap/app.php`. Admin route files are written *relative to the admin group*: the loader adds the `admin` prefix and the `auth.admin` middleware (except `Modules/Admin/Auth`, which is guest-only). There is no `routes/web.php`.
- **A service used by two or more modules** goes in `app/Services/`. An admin module may use top-level services; a top-level module never imports `App\Modules\Admin\*`.
- **Authentication** is resolved by a custom guard (`SessionTokenGuard`), so `auth()->user()`, `Auth::id()` and `@auth` work while the real session lives in `user_sessions`, keyed by a signed cookie. `auth()->guard()->session()` returns the current session row.

For the request lifecycle, layers and caching → **[`docs/architecture.md`](docs/architecture.md)**

---

## Development Workflow

1. **Read before writing.** Grep for how the codebase already solves the problem.
2. New feature → new module folder (or a controller in an existing one). Routes go in that module's `<module>_routes.php`, with **no `/api` prefix**, using `[Controller::class, 'method']`.
3. New queries → a repository method.
4. Make the change, then `./vendor/bin/pint`.
5. **Verify it actually works** — hit the route on the running site (see the note on URLs below). Static checks will not catch this repo's failure modes.
6. If you moved or renamed a class: regenerate the autoloader (see below), then `php artisan optimize:clear`.

---

## Build / Test / Lint Commands

```bash
php artisan serve                      # dev server
./vendor/bin/pint                      # format (run before finishing any task)
php artisan test                       # PHPUnit
php artisan settings:set smtp_password   # write one setting (secrets encrypted); prompts hidden if no value
php artisan db:seed --class=SettingSeeder  # add missing settings rows, never overwrites
npm run build                          # Tailwind/Vite build (needed after adding new utility classes)
npm run dev
```

> ⚠️ **Autoload:** the system `composer` (2.0.14) cannot parse `enum` files and silently drops `SortDirection` from the classmap, which breaks every page. Use a current Composer (2.7+): `php composer.phar dump-autoload -o`.
>
> ⚠️ **URLs:** the deployed site does not rewrite pretty URLs under `/laravel/laravel/public/`; request routes as `…/public/index.php/<route>`.
>
> ⚠️ `phpunit.xml` has the SQLite lines **commented out**, so `php artisan test` runs against the **real MySQL database**. Configure a scratch database before running the suite.

```bash
php artisan route:list --except-vendor
php artisan migrate:fresh --seed         # only on a database you can wipe
php artisan tinker
php artisan optimize:clear
php artisan view:clear                   # after editing Blade
```

---

## Database

MySQL/MariaDB with **the same 16 tables as the Next app** (Postgres): `users`, `user_accounts`, `user_sessions`, `user_two_factors`, `user_devices`, `user_verifications`, `user_login_links`, `user_passkeys`, `user_activities`, `settings`, `pages`, `seos`, `blogs`, `notes`, `contact_messages`, `email_templates`. Migrations and `DataSeeder` (rows from Next's `db/seed/*.sql`) live in `database/`. Laravel adds only infrastructure tables (`sessions`, `cache`, `jobs`, `migrations`, …).

Conventions:

- **UUID PKs** on the auth tables (`char(36) ascii_bin`, `HasUuids`); `serial` (INT AUTO_INCREMENT) on the content tables.
- **`ascii_bin` collation** on token / UUID / base64url columns. Security-critical — never relax it.
- **`DATETIME`, not `TIMESTAMP`.** Keep `APP_TIMEZONE=UTC`.
- `text` in Postgres is `TEXT` here, except unique/indexed columns (`VARCHAR`) and HTML bodies (`LONGTEXT`).
- Roles are strings: `USER`, `ADMIN`, `SUPER_ADMIN` (`App\Constants\UserRole`). Status: `active` / `inactive` (`UserStatus`).
- `smtp_password`, `google_client_secret` and `google_recaptcha_secret_key` are **encrypted at rest** (`App\Helpers\Encryption`, AES-256-GCM, same layout as Next, key derived from `ENCRYPTION_KEY`); `SettingRepository` encrypts on write and decrypts on read. Set them with `php artisan settings:set <key> [value]` rather than typing them into the admin form.
- `settings` rows use Next's plain keys (`smtp_host`, `user_email_verify`, …); `SettingRepository::configOverrides()` maps them into Laravel config. Date formats are stored as date-fns patterns and converted to PHP patterns on load.
- The `users` table has **no** password column. Passwords live in `user_accounts` where `provider_id = 'credential'`.

---

## Responses and the Frontend Contract

Every AJAX endpoint returns the Next envelope, built with `App\Helpers\Response` (same function names as Next's `response.ts`):

```json
{ "status": 1, "message": "…", "data": {} }
```

- `status` is `1`/`0`, **not** the HTTP status. `data` defaults to `[]`; extras live *inside* `data`.
- **Responses never carry navigation** (`next`, `url`). The view decides what happens after success with `data-next` (`load`, `refresh`, `table_refresh`, `reload`, `redirect`, `hide_modal`, `show_modal_view`) and `data-next-url` on the form or button that triggers the request; `app.js` runs it. Flags such as `requires_tfa` / `requires_verification` are data, not navigation.
- Use **HTTP 200** for any failure the page handles itself (it reads `data`, or shows the message inline): jQuery only calls the caller's callback for 2xx. Validation (422), auth (401), CSRF (419) and rate limit (429) use their real codes and are rendered as the envelope for AJAX requests (`bootstrap/app.php`).
- **DataTables endpoints** are the one exception: they return DataTables' own JSON (`recordsTotal`, `data`, `draw`) from `Helpers\Pagination::getDataTable()`. Build rows in a service and render HTML cells with Blade partials, not string concatenation.
- Use the existing helpers — `app.ajaxForm`, `app.ajaxFileForm`, `app.ajaxPost`, `app.confirmAction`, `app.dataTable`, `app.showMessage` — never raw `$.ajax` for forms.

Details and examples → **[`docs/api.md`](docs/api.md)**.

---

## Authentication Overview

Session-cookie based, not Laravel's built-in auth:

- Login verifies the password against `user_accounts` (argon2id), issues a row in `user_sessions` and sets a signed cookie.
- `SessionTokenGuard` resolves the user from that cookie on each request, cached ~300s (`SessionService::invalidateUserCache()` after changing a user).
- Cookies are named `{APP_UID}_{name}` and signed with **`ENCRYPTION_KEY`** (a separate 32-byte env var, *not* `APP_KEY`) via `SignedCookie`.
- Admin and user share one `users` table and one session model; `auth.admin` additionally requires an admin role and permission.

Flows: password, email OTP, forgot/reset password, 2FA (TOTP / email OTP / backup codes) with trusted devices, magic login links, WebAuthn passkeys, Google OAuth. Endpoints are ordinary web routes under `/auth/*` and `/admin/auth/login`.

For the cookie table, guard internals, 2FA and passkey ceremonies → **[`docs/authentication.md`](docs/authentication.md)**

---

## Frontend Overview

Server-rendered Blade + Tailwind.

- **Layouts:** `layouts/blank` (auth pages), `layouts/main` (site shell, PJAX-aware); admin mirrors them in `modules/admin/layouts/`.
- **New pages use Next's class vocabulary** through the `x-ui.*` components (`button`, `input`, `textarea`, `select`, `label`, `card`, `card-header`, `card-title`, `card-content`, `badge`, `alert`, `table`/`tr`/`th`/`td`, `pagination`). The design tokens (`bg-card`, `text-muted-foreground`, `border-border`, …) come from `resources/css/next-theme.css`. Run `npm run build` after using a new utility class.
- **JS lives in `public/assets/js/`** and is included with plain `<script>`. `resources/js/app.js` is not used by any view.
- Pass URLs in from Blade (`route()`, `data-*`), never hardcode paths in JS.

Details → **[`docs/frontend.md`](docs/frontend.md)**

---

## Coding Rules & Conventions

**PHP**

- PSR-12 via Pint. Constructor property promotion for dependencies:
  ```php
  public function __construct(
      protected SessionService $sessions,
      protected ActivityService $activity,
  ) {}
  ```
- Type-hint parameters and return types; array shapes in docblocks (`@return array{ok: bool, message: string}`).
- Prefer constructor injection over `app(Foo::class)`.
- Controllers call `parent::__construct()` (they extend `App\Http\Controllers\Controller`, or `App\Modules\Admin\Controllers\Controller` for admin, which share `$general` and settings with the views).
- **FormRequests** validate; do not override `failedValidation()` — the exception handler renders the envelope.
- Use constants for roles, statuses, activity types and blog categories (`UserRole`, `UserStatus`, `UserActivity`, `BlogCategory`).
- Services return `['ok' => bool, 'message' => string, …extra]`; `Response::sendResult()` maps `ok` to `status` and nests extras in `data`.
- Comments explain **why**, not what.

**Blade**

- Auth pages `@extends('layouts.blank')`; admin pages `@extends('modules.admin.layouts.main')`.
- Shared admin partials: `modules/admin/partials/{status-badge,row-actions,account-form,account-profile,editor,…}`.

**JavaScript**

- Vanilla plus the existing jQuery helpers. No new framework or plugin dependency.
- Pass URLs in via `data-*` attributes or an `init({...})` call.

---

## Naming Conventions

| Thing | Convention | Example |
|---|---|---|
| Class | `StudlyCase` | `LoginLinkService` |
| Method / variable | `camelCase` | `verifyLoginChallenge()` |
| DB table | `snake_case` plural | `user_login_links` |
| Route path | `kebab-case`, no `/api` prefix | `/auth/verify-account` |
| Route name | slash style, matches the path | `->name('admin/user/view')` |
| Module route file | `<module>_routes.php` | `Modules/Blog/blog_routes.php` |
| Blade view | `kebab-case.blade.php` under `modules/<module>/` | `modules/auth/verify-tfa.blade.php` |
| JS file | `kebab-case.js` | `login-link.js` |
| Cookie | `{APP_UID}_{name}` | `demo_session_token` |
| Cache key | `colon:separated` | `auth:session:{token}` |

Route **names** are load-bearing: `SeoMetaRepository::metaForRoute()` looks up `seos.url` by the current route name (`home`, `contact`, `blog`; `blog/*` matches `blog/show`).

---

## Project-Specific Rules

1. **Never store a password on `users`.**
2. **Never weaken `ascii_bin`** on token/UUID columns.
3. **Never sign cookies with `APP_KEY`.** Use `SignedCookie`.
4. **Never hardcode TTLs or attempt caps.** They live in `config/auth_next.php`.
5. **Password change and reset revoke every session**, including the current one — the UI says so.
6. **Keep auth responses enumeration-safe.** Unknown and known email must be indistinguishable in message, shape and (where practical) timing. Use `AuthService::dummyPasswordCheck()` on failure paths.
7. **OTP and 2FA attempt counters increment *before* comparison.**
8. **Hash high-entropy tokens with SHA-256, not argon2id.** Argon2id is for passwords, OTPs and backup codes.
9. **Rate-limit every new public endpoint** (`auth.throttle:{name}` for auth, `throttle:5,15` for forms).
10. **Log security-relevant actions** via `ActivityService::log()` with a `UserActivity` constant.
11. **Admin routes are permission-gated and fail closed.** `PermissionService::requiredKeys()` maps a route to a key in the permission list (`X/list` → `X`, `X/save` → `X/create` or `X/update`, irregular ones in `GUARDED_BY`); an unmapped route is refused. Add new admin routes to that map/list — `PermissionAuditTest` fails otherwise.
12. **Admin screens are role-scoped.** `AccountManagementService` takes the role it may touch; look accounts up with `UserRepository::findByIdAndRole()`, never by id alone. Notes are always looked up with the owning user's id.
13. **Never build HTML in models, repositories or with string concatenation in services.** Render Blade partials.

---

## Common Pitfalls

| Pitfall | Consequence | Avoid by |
|---|---|---|
| Regenerating the autoloader with the system `composer` | `Class "SortDirection" not found` on every page | Use a current `composer.phar` |
| Returning `next` / `url` from a controller | Navigation logic split between server and view | Put `data-next` on the form/button |
| Returning non-200 on a failure the page handles itself | The frontend callback never fires; `data` is lost | `Response::sendMessage($msg, 0)` or `sendResponse(200, …)` |
| Trusting `Auth::login()` / `Auth::logout()` | `SessionTokenGuard` implements only `Guard`; these fatal | `SessionService::issue()` / `revoke()` |
| Adding a column to `users` for auth state | Wrong table; the schema mirrors Next | Use the existing `user_*` table for that concern |
| Using a utility class no view had before | Silently unstyled until the CSS is rebuilt | `npm run build` |
| Editing `resources/js/app.js` expecting a browser change | That bundle is never loaded | Edit `public/assets/js/*.js` |
| Forgetting `parent::__construct()` in a controller | `$general` / settings missing → view errors | Always call it |
| A distinct error message on a failure path | Enables user enumeration | Keep messages generic; log the real reason |
| Editing Blade and not seeing the change | Compiled views are cached | `php artisan view:clear` |
| Changing a setting and not seeing it | Settings are cached under key `setting` | `SettingRepository::setMany()` clears it, or `php artisan cache:clear` |
| Assuming `php artisan test` is isolated | Runs against the **real** database | Configure a test DB first |

---

## AI Agent Instructions

**Before changing anything:** grep for the existing solution; read `docs/local/*.md` if present (plans and known gaps); for auth, sessions, cookies, hashing, rate limiting and 2FA read `docs/authentication.md` first.

**While working:** change the minimum necessary; reuse `Response`, `SignedCookie`, `ClientInfo`, `ActivityService`; match surrounding style; commit per completed task.

**Before reporting done:** run `./vendor/bin/pint`; **actually exercise the change** on the running site (login, hit the route, confirm status and body); if you could not verify something, say so; report failures with real output.

---

## References

| Document | Contents |
|---|---|
| [`docs/architecture.md`](docs/architecture.md) | Modules, layers, request lifecycle, guard resolution, caching |
| [`docs/authentication.md`](docs/authentication.md) | Cookies, sessions, every auth flow, 2FA, passkeys, OAuth, middleware |
| [`docs/api.md`](docs/api.md) | Envelope, HTTP status rules, validation, rate limits, endpoint list |
| [`docs/new_module.md`](docs/new_module.md) | Step-by-step checklist for adding a module |
| [`docs/frontend.md`](docs/frontend.md) | Layouts, x-ui components, JS helpers, DataTables, PJAX |
| `docs/local/*` | Gitignored working notes (missing features, module plan) |

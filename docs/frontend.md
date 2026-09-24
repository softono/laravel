# Frontend

Server-rendered Blade + Tailwind CSS v4, with vanilla JS and jQuery helpers. No SPA framework.

Summary in [`../AGENTS.md`](../AGENTS.md#frontend-overview).

---

## Stack

| Concern | Choice |
|---|---|
| Markup | Blade; module views in `resources/views/modules/<module>/` (`modules/admin/<module>/` for the admin panel) |
| CSS | Tailwind CSS v4 built by Vite from `resources/css/app.css` (+ `next-theme.css`, `common.css`) |
| Components | `resources/views/components/ui/*` — anonymous Blade components wrapping Next's shadcn class strings |
| Icons | Boxicons (`bx bx-…`, bundled by Vite) |
| Tables | jQuery DataTables 2 with the Tailwind styling integration |
| JS | `public/assets/js/*.js`, plain `<script>` tags — **not** bundled by Vite |
| Interactivity | jQuery (`app.*` helpers, `jquery-validate`), PJAX page loading. No Alpine, Bootstrap or other framework |

Vite compiles **CSS only** (`@vite('resources/css/app.css')`); there is no JS bundle. After using a Tailwind utility class that no view used before, run `npm run build`.

---

## Layouts

| Layout | Used by |
|---|---|
| `layouts/blank` | Auth pages (login, register, verify, …) |
| `layouts/main` | Site shell: home, blog, pages, contact, dashboard, notes, account area |
| `modules/admin/layouts/blank` | Admin auth pages |
| `modules/admin/layouts/main` | Admin panel (sidebar + navbar) |

Both `main` layouts are PJAX-aware and share `common/datatables-css` / `common/datatables-js`, `common/message_alert`, and the toast/modal containers. Every controller extends `App\Http\Controllers\Controller` (or the admin subclass), whose constructor shares `$general` and the settings with the views.

Page skeleton:

```blade
@extends('layouts.main')
@section('title')
    Notes
@endsection
@section('content')
    …
@endsection
@push('scripts')
    <script>
        documentReady(function () { … });
    </script>
@endpush
```

---

## Design system: Next's classes via `x-ui`

New pages use the same utility classes as the Next app. `resources/css/next-theme.css` copies Next's theme variables (`--background`, `--card`, `--muted-foreground`, `--border`, `--primary`, `--destructive`, `--success`, radius and shadow tokens, dark mode) so `bg-card`, `text-muted-foreground`, `border-border`, `text-destructive`, … resolve.

The components carry Next's exact class strings:

| Component | Props |
|---|---|
| `<x-ui.button>` | `variant` (`default`, `destructive`, `success`, `outline`, `secondary`, `ghost`, `link`), `size` (`default`, `sm`, `lg`, `icon`, `icon-sm`), `href` (renders `<a>`), `type` (default `button`; pass `submit`) |
| `<x-ui.input>`, `<x-ui.textarea>`, `<x-ui.select>`, `<x-ui.label>`, `<x-ui.checkbox>` | native attributes pass through; `:checked="…"` / `:readonly="…"` for conditional attributes |
| `<x-ui.password-input id="…">` | Input with a show/hide button (`data-password-toggle`, handled in `app.ui`) |
| `<x-ui.card>`, `card-header`, `card-title`, `card-description`, `card-content` | `class` merges |
| `<x-ui.badge>` | `variant` (`default`, `secondary`, `destructive`, `success`, `outline`) |
| `<x-ui.alert>`, `alert-title`, `alert-description` | `variant` (`default`, `destructive`, `success`, `warning`) |
| `<x-ui.modal id="…">`, `dialog-header`, `dialog-title`, `dialog-footer` | Dialog shell (`size`: `md`, `lg`, `xl`; `content-id` for AJAX-filled modals); see [Modals](#modals) |
| `<x-ui.page-header title="…" :crumbs="[['Dashboard', route('admin/dashboard')], ['Users']]">` | Page title + breadcrumb trail (last crumb has no link) |
| `<x-ui.theme-switch>` | Light / dark / system picker; see [Theme switch](#theme-switch) |
| `<x-ui.table>`, `tr`, `th`, `td` | plain tables |
| `<x-ui.pagination :paginator="$posts" />` | Previous / Next for a Laravel paginator |

```blade
<x-ui.card>
    <x-ui.card-header><x-ui.card-title>Account Information</x-ui.card-title></x-ui.card-header>
    <x-ui.card-content>
        <x-ui.button :href="route('account/update')" variant="ghost" size="sm" class="pjax">Go</x-ui.button>
    </x-ui.card-content>
</x-ui.card>
```

There is no separate legacy stylesheet: every screen (public, user and admin) is built from these components and plain Tailwind utilities with the theme tokens (`bg-card`, `text-muted-foreground`, `border-border`, `bg-sidebar`, …). Do not hardcode palette colours (`slate-*`, `rose-*`); they do not follow dark mode. Component tags cannot contain Blade directives or unquoted `{{ }}`: use bound attributes (`:checked="$x"`, `:readonly="(bool) $y"`) instead of `@if … checked @endif`.

Shared admin partials in `resources/views/modules/admin/partials/`: `status-badge`, `row-actions`, `account-form`, `account-profile`, `recent-sessions`, `recent-activity`, `editor` (Summernote with image upload).

---

## `app.*` helpers (`public/assets/js/app.js`)

They speak the [response envelope](api.md) and read the follow-up from the triggering element.

| Helper | Purpose |
|---|---|
| `app.ajaxForm(form, cb?)` | POST a form (serialized). Default callback: message, then the form's `data-next` |
| `app.ajaxFileForm(form, cb?)` | Same with `FormData` (file uploads) |
| `app.ajaxPost(url, data, cb?)` / `app.ajaxGet(url, cb?)` | Plain requests (CSRF token added for POST) |
| `app.confirmAction(button)` | Confirmation dialog, then POST `data-action` with `data-id`; follow-up from the button's `data-next` |
| `app.dataTable(selector, {url, columns, order})` | Server-side DataTable (Tailwind integration); sets the global `datatableObj` |
| `app.showMessage(msg, 'success' \| 'error')` | Toast |
| `app.showModalView(url)` / `app.openModal($m)` / `app.closeModal($m)` / `app.isModalOpen($m)` | Modals (see below) |
| `app.loadScript(url, cb)` / `app.addCSS([...])` | Lazy third-party assets (Chart.js, Summernote, Cropper) |

### Declarative UI behaviours (`app.ui`)

Dropdowns, collapsible menus, the admin sidebar, tabs, modals, alerts and the theme switch are plain jQuery, delegated from `document` in `app.js`, so they also work on PJAX-loaded content. Markup opts in with data attributes; toggled elements start with the Tailwind `hidden` class.

| Attributes | Behaviour |
|---|---|
| `data-dropdown` wrapper, `data-dropdown-toggle` button, `data-dropdown-menu` | Click toggles the menu; a click outside or on a link inside closes it |
| `data-collapse-toggle="#id"` (+ `data-toggle-icon` icons) | Toggles `hidden` on `#id` and on the icons (menu/close swap) |
| `data-sidebar-toggle="open\|close"` | Admin sidebar (`#layout-menu`) and `#sidebar-backdrop` |
| `data-tabs` (with `data-tabs-active` / `data-tabs-inactive` class strings), `data-tab="x"`, `data-tab-panel="x"` | Tabs; render the first tab active and the other panels `hidden` |
| `data-menu-toggle` on a sidebar link | Toggles `open` on its `<li>` (submenu group) |
| `data-modal-open="#id"`, `data-modal-dismiss` | Open / close a `[data-modal]` |
| `data-alert-dismiss` inside an `x-ui.alert` | Removes the alert |
| `data-password-toggle="#input"` | Show/hide a password field |
| `data-theme-switch` | Colour theme + light/dark/system picker (below) |

Tailwind also scans `public/assets/js`, so utility classes written in JS strings (toasts, DataTables classes) are generated; run `npm run build` after adding one.

### Modals

`<x-ui.modal id="note-modal">…</x-ui.modal>` renders a hidden `[data-modal]` overlay (backdrop, panel, close button). Open it with `data-modal-open="#note-modal"` or `app.openModal($('#note-modal'))`; the backdrop, any `data-modal-dismiss` element and Escape close it. State lives in `data-state="open|closed"` and the `hidden`/`flex` classes. The layouts include one shared `#common-modal` whose content (`#common-modal-content`) is loaded by `app.showModalView(url)`; the `hide_modal` follow-up closes it.

### Theme switch

`<x-ui.theme-switch />` is Next's "switchcn" `ThemeSwitcher`: a palette button that opens a popover with a search box, a theme count, a light/system/dark cycle button, a random-theme button and the list of 43 colour themes (four swatches each, a check on the active one). It is placed in the public/user navbar (desktop and mobile), the admin navbar, and floats top-right on both `blank` (auth) layouts (`<x-ui.theme-switch float />`).

**Themes.** `resources/themes/catalog.json` (name, label, swatches; the order of Next's registry) and `resources/themes/<name>.json` (the same shadcn theme files Next ships) are read by `App\Helpers\ThemeCatalog`. It only ever accepts names from the catalog. `default` is the token set in `next-theme.css` and needs no override. Add a theme by dropping its JSON in `resources/themes/` and adding a catalog row.

**Endpoints** (public, envelope, `throttle:60,1`): `GET /theme` → `data.themes`; `GET /theme/{name}` → `data.{name, css, font_href}` (404 for an unknown name). The picker loads the list on first open and a theme's CSS when picked, then caches it in memory.

**State.**

| Key (localStorage **and** plain cookie, one year) | Values |
|---|---|
| `app-theme` | a catalog name (default: the admin's `default_theme` setting, else `default`) |
| `app-color-mode` | `light` \| `dark` \| `system` (default `system`) |

The cookies are exempt from Laravel's cookie encryption (`bootstrap/app.php`) so the server can read them; a value that is not in the catalog is ignored. Storage access is wrapped in try/catch.

**No flash.** `common/theme-init` (included **after** the Vite stylesheet, so its `:root` overrides win) renders `<style id="app-theme-vars" data-theme-name="…">` with the theme's CSS variables and a Google Fonts link when the theme uses web fonts, then a tiny inline script applies the `dark` class before first paint. If the remembered theme differs from the rendered one (cookie cleared, or picked in another tab), `app.ui.theme.init()` applies it.

**Behaviour** (`app.ui.theme` in `app.js`): a pick calls `setTheme(name)` (store, cookie, inject CSS/fonts, mark the active row, update the trigger title); the cycle button steps light → system → dark; `system` follows `prefers-color-scheme` live; other tabs sync through the `storage` event; PJAX needs no re-binding (delegated handlers). The cycle and shuffle buttons carry `data-keep-open` so the dropdown stays open; picking a theme closes it.

**Admin default.** Settings → General → *Default Theme* stores `default_theme`; it applies to visitors without their own pick.

**Tokens.** Themes override the tokens in `next-theme.css` (colours, `--radius`, fonts, shadows, `--tracking-normal`), so use the tokens (`bg-background`, `bg-card`, `text-foreground`, `border-border`, `bg-sidebar`, …) and never palette colours, or the page will not follow the theme. DataTables' classes are token-based in `app.styleDataTables()`.

### Follow-up actions

The server never returns `next` or `url`. Put them on the element:

```blade
<form id="ajax-form" action="{{ route('admin/blog/save') }}" data-next="load" data-next-url="{{ route('admin/blog') }}">
<button onclick="app.confirmAction(this);" data-action="{{ route('notes/delete') }}" data-id="{{ $row->id }}" data-next="table_refresh">
```

Actions: `load`, `refresh`, `table_refresh`, `redirect`, `reload`, `hide_modal`, `show_modal_view` (comma-separate to combine). Programmatic requests can pass their own callback and call `app.ajaxSuccess(response, {next, url})`.

### DataTables

```blade
<table class="w-full text-sm" id="data-table">
    <thead><tr><th>#</th><th>Title</th><th>Actions</th></tr></thead>
</table>
@push('scripts')
    <script>
        documentReady(function () {
            app.dataTable('#data-table', {
                url: '{{ route('admin/blog/list') }}',
                columns: [{data: "id", visible: false}, {data: "title"}, {data: "action", orderable: false}],
                order: [[1, "asc"]],
            });
        });
    </script>
@endpush
```

The endpoint calls a service that returns `Pagination::getDataTable()` output with each row's HTML cells rendered from Blade partials. Only columns declared `orderable` (and named with a plain identifier) can sort the query.

---

## JavaScript conventions

- Vanilla JS plus the existing jQuery helpers; no new framework or plugin dependency.
- Interactive widgets: an IIFE with `'use strict'`, guarded against a missing target element, exposing a named global that the page initialises:

```blade
@push('scripts')
    <script src="{{ asset('assets/js/auth/login-link.js') }}"></script>
    <script>
        loginLink.init({ pollUrl: '{{ url('/auth/login-link/poll') }}' });
    </script>
@endpush
```

- Pass URLs from Blade (`route()`, `url()`, `data-*`); never hardcode paths in JS.
- CSRF: the layouts define `CSRF_NAME` / `CSRF_TOKEN` and expose `<meta name="csrf-token">`; `app.ajaxPost` / `app.ajaxForm` attach the token.

---

## PJAX

Links with the `pjax` class load their page into the layout without a full reload (`public/assets/js/pjax.js`); `pjax.loadPage(url)` does the same programmatically and is what the `load` / `refresh` follow-ups call. Add `data-pjax-layout="blank"` to a link that leads to a `blank`-layout page, and use plain links (no `pjax`) for logout and downloads.

---

## Troubleshooting

| Symptom | Cause |
|---|---|
| A Tailwind class does nothing | It was not in the last build — run `npm run build` |
| The callback never fires on a failure | The endpoint returned non-2xx; failures the page handles itself must be HTTP 200 (see [`api.md`](api.md#http-status)) |
| The page does not navigate after saving | The form or button has no `data-next` |
| A table does not reload after delete | The delete button has no `data-next="table_refresh"`, or the table was not created with `app.dataTable` |
| `$general` undefined in a view | The controller did not call `parent::__construct()` |
| Blade change not visible | `php artisan view:clear` |

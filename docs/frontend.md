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
| Icons | Boxicons (`bx bx-…`) |
| Tables | jQuery DataTables 2 with the Tailwind styling integration |
| JS | `public/assets/js/*.js`, plain `<script>` tags — **not** bundled by Vite |
| Interactivity | jQuery (`app.*` helpers, `jquery-validate`), Alpine.js in the shells, PJAX page loading |

Vite compiles **CSS only** (`@vite(['resources/css/app.css', 'resources/js/app.js'])`); `resources/js/app.js` is not used by any view. After using a Tailwind utility class that no view used before, run `npm run build`.

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
| `<x-ui.button>` | `variant` (`default`, `destructive`, `outline`, `secondary`, `ghost`, `link`), `size` (`default`, `sm`, `lg`, `icon`, `icon-sm`), `href` (renders `<a>`), `type` |
| `<x-ui.input>`, `<x-ui.textarea>`, `<x-ui.select>`, `<x-ui.label>` | native attributes pass through |
| `<x-ui.card>`, `card-header`, `card-title`, `card-description`, `card-content` | `class` merges |
| `<x-ui.badge>` | `variant` (`default`, `secondary`, `destructive`, `success`, `outline`) |
| `<x-ui.alert>` | `variant` (`default`, `destructive`) |
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

The older admin markup (`.card`, `.btn-primary`, `.form-input`, `.breadcrumb-box`, …) is defined in `resources/css/app.css` and still used by the shells; write **new** content with `x-ui`.

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
| `app.showModalView(url)` / `app.openModal($m)` / `app.closeModal($m)` | Modals |
| `app.loadScript(url, cb)` / `app.addCSS([...])` | Lazy third-party assets (Chart.js, Summernote, Cropper) |

### Follow-up actions

The server never returns `next` or `url`. Put them on the element:

```blade
<form id="ajax-form" action="{{ route('admin/blog/save') }}" data-next="load" data-next-url="{{ route('admin/blog') }}">
<button onclick="app.confirmAction(this);" data-action="{{ route('notes/delete') }}" data-id="{{ $row->id }}" data-next="table_refresh">
```

Actions: `load`, `refresh`, `table_refresh`, `list_refresh`, `redirect`, `reload`, `hide_modal`, `show_modal_view` (comma-separate to combine). Programmatic requests can pass their own callback and call `app.ajaxSuccess(response, {next, url})`.

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

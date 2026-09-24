# Adding a Module

A worked checklist for a new feature, using a hypothetical `Ticket` module. Layout rules live in [`architecture.md`](architecture.md); this is the order to do things in.

## 1. Table and model

```bash
php artisan make:migration create_tickets_table
php artisan make:model Models/Ticket
```

Mirror the Next app's column names and types if the table exists there (see `AGENTS.md` → Database). The model holds schema only: `$fillable`, casts, relationships. No queries, no business logic.

## 2. Repository

`app/Repositories/TicketRepository.php` is the only place that queries `Ticket`:

```php
class TicketRepository
{
    public function forUser(string $userId): Collection
    {
        return Ticket::where('user_id', $userId)->latest()->get();
    }

    public function datatable(array $post): array   // DataTables endpoints use Helpers\Pagination
    {
        return app(Pagination::class)->getDataTable(DB::table('tickets')->select([...]), $post);
    }
}
```

## 3. Module folder

```
app/Modules/Ticket/
  Controllers/TicketController.php
  Requests/SaveTicketRequest.php
  Services/TicketService.php
  ticket_routes.php
```

Admin screens go in `app/Modules/Admin/Ticket/` instead. The loader in `bootstrap/app.php` picks up `Modules/*/*_routes.php` automatically; for admin modules it adds the `admin` prefix and the `auth.admin` middleware, so write those routes relative to the group.

## 4. Routes

No `/api` prefix; route names are slash-style and match the path:

```php
Route::middleware(['device.uid', 'auth.user'])->prefix('tickets')->group(function () {
    Route::get('/', [TicketController::class, 'index'])->name('tickets');
    Route::post('list', [TicketController::class, 'list'])->name('tickets/list');
    Route::post('save', [TicketController::class, 'save'])->name('tickets/save');
});
```

Public write endpoints need a throttle (`auth.throttle:{tier}` or `throttle:5,15`).

## 5. Request → controller → service

- The **FormRequest** validates; it does not override `failedValidation()`.
- The **controller** is thin: call one service method, return `Response::sendResult($result)` (or a view).
- The **service** returns `['ok' => bool, 'message' => string, …extras]` and talks to repositories. Log security-relevant actions with `ActivityService::log()`.
- Always call `parent::__construct()` in a controller that declares its own constructor.

## 6. Views

`resources/views/modules/ticket/index.blade.php` (`modules/admin/ticket/…` for admin). Use the `x-ui.*` components and Next's class names for new pages. Put the post-success behaviour on the element, never in the response:

```blade
<form action="{{ route('tickets/save') }}" method="post" data-next="load" data-next-url="{{ route('tickets') }}">
```

Tables use `app.dataTable('#data-table', {url, columns, order})`; render row cells with Blade partials.

## 7. Admin only: permissions

1. Add the keys (`admin/ticket`, `admin/ticket/create`, `admin/ticket/update`, `admin/ticket/delete`) to `PermissionService::getPermissionListData()`.
2. `X/list` and `X/save` resolve automatically; anything else (`change-status`, `save-image`, …) goes in `PermissionService::GUARDED_BY`.
3. Add the sidebar link, guarded by the group key.
4. `php artisan test` — `PermissionAuditTest` fails until every admin route is mapped.

## 8. Verify

```bash
./vendor/bin/pint
php artisan route:list --path=tickets
php artisan test
```

Then hit the routes on the running site (signed in, and signed out) and check the status code and the `{status, message, data}` body. Add a feature test for anything with logic.

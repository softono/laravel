<x-ui.button variant="ghost" size="icon-sm" title="Log out this device"
    onclick="app.confirmAction(this);" data-action="{{ route($route) }}" data-id="{{ $id }}" data-next="table_refresh">
    <i class="bx bx-log-out"></i>
</x-ui.button>

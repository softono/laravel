<div class="flex items-center gap-2">
    @if (\Illuminate\Support\Facades\Route::has($base.'/view') && $viewer->hasPermission($base.'/view'))
        <x-ui.button variant="ghost" size="icon-sm" href="{{ route($base.'/view', ['id' => $id]) }}" class="pjax" title="View">
            <i class="bx bxs-show"></i>
        </x-ui.button>
    @endif
    @if ($viewer->hasPermission($base.'/update'))
        <x-ui.button variant="ghost" size="icon-sm" href="{{ route($base.'/update', ['id' => $id]) }}" class="pjax" title="Update">
            <i class="bx bxs-edit"></i>
        </x-ui.button>
    @endif
    @if ($viewer->hasPermission($base.'/delete'))
        <x-ui.button variant="ghost" size="icon-sm" title="Delete"
            onclick="app.confirmAction(this);" data-action="{{ route($base.'/delete') }}" data-id="{{ $id }}"
            data-next="table_refresh">
            <i class="bx bxs-trash"></i>
        </x-ui.button>
    @endif
</div>

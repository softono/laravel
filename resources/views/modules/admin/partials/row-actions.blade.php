<div class="flex items-center gap-2">
    @if (\Illuminate\Support\Facades\Route::has($base.'/view') && $viewer->hasPermission($base.'/view'))
        <a href="{{ route($base.'/view', ['id' => $id]) }}" class="text-body pjax" title="View"><i class="bx bxs-show icon-base"></i></a>
    @endif
    @if ($viewer->hasPermission($base.'/update'))
        <a href="{{ route($base.'/update', ['id' => $id]) }}" class="text-body pjax" title="Update"><i class="bx bxs-edit icon-base"></i></a>
    @endif
    @if ($viewer->hasPermission($base.'/delete'))
        <button type="button" class="text-body cursor-pointer border-0 bg-transparent" title="Delete"
            onclick="app.confirmAction(this);" data-action="{{ route($base.'/delete') }}" data-id="{{ $id }}"
            data-next="table_refresh">
            <i class="bx bxs-trash icon-base"></i>
        </button>
    @endif
</div>

<div class="flex items-center gap-2">
    @if ($viewer->hasPermission('admin/email-template/update'))
        <x-ui.button variant="ghost" size="icon-sm" href="{{ route('admin/email-template/update', ['id' => $row->id]) }}" class="pjax" title="Update">
            <i class="bx bxs-edit"></i>
        </x-ui.button>
    @endif
    @if ($viewer->hasPermission('admin/email-template/view'))
        <x-ui.button variant="ghost" size="icon-sm" href="{{ route('admin/email-template/view', ['id' => $row->id]) }}" target="_blank" title="Preview">
            <i class="bx bxs-show"></i>
        </x-ui.button>
    @endif
</div>

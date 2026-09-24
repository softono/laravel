<div class="flex items-center gap-2">
    @if ($viewer->hasPermission('admin/page/update'))
        <x-ui.button variant="ghost" size="icon-sm" href="{{ route('admin/page/update', ['id' => $row->id]) }}" class="pjax" title="Update">
            <i class="bx bxs-edit"></i>
        </x-ui.button>
    @endif
    @if ($row->status === \App\Constants\UserStatus::ACTIVE)
        <x-ui.button variant="ghost" size="icon-sm" href="{{ route('page', ['slug' => $row->slug]) }}" target="_blank" title="View">
            <i class="bx bxs-show"></i>
        </x-ui.button>
    @endif
</div>

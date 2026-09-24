<div class="flex items-center gap-2">
    @if ($viewer->hasPermission('admin/page/update'))
        <a href="{{ route('admin/page/update', ['id' => $row->id]) }}" class="text-body pjax" title="Update"><i class="bx bxs-edit icon-base"></i></a>
    @endif
    @if ($row->status === \App\Constants\UserStatus::ACTIVE)
        <a href="{{ route('page', ['slug' => $row->slug]) }}" target="_blank" class="text-body" title="View"><i class="bx bxs-show icon-base"></i></a>
    @endif
</div>

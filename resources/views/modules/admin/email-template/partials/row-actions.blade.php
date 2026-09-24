<div class="flex items-center gap-2">
    @if ($viewer->hasPermission('admin/email-template/update'))
        <a href="{{ route('admin/email-template/update', ['id' => $row->id]) }}" class="text-body pjax" title="Update"><i class="bx bxs-edit icon-base"></i></a>
    @endif
    @if ($viewer->hasPermission('admin/email-template/view'))
        <a href="{{ route('admin/email-template/view', ['id' => $row->id]) }}" target="_blank" class="text-body" title="Preview"><i class="bx bxs-show icon-base"></i></a>
    @endif
</div>

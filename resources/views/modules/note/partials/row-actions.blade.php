<div class="flex items-center gap-1">
    <x-ui.button variant="ghost" size="icon-sm" class="note-edit" title="Edit"
        data-id="{{ $row->id }}" data-title="{{ $row->title }}" data-note="{{ $row->note }}">
        <i class="bx bxs-edit"></i>
    </x-ui.button>
    <x-ui.button variant="ghost" size="icon-sm" class="text-destructive" title="Delete"
        onclick="app.confirmAction(this);" data-action="{{ route('notes/delete') }}" data-id="{{ $row->id }}" data-next="table_refresh">
        <i class="bx bxs-trash"></i>
    </x-ui.button>
</div>

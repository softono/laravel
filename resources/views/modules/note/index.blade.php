@extends('layouts.user')
@section('title')
    Notes
@endsection
@section('content')
    <div>
        <x-ui.card>
            <x-ui.card-header class="!grid-cols-[1fr_auto] items-center">
                <x-ui.card-title class="text-lg">My Notes</x-ui.card-title>
                <x-ui.button id="note-add"><i class="bx bx-plus"></i> Add note</x-ui.button>
            </x-ui.card-header>
            <x-ui.card-content class="overflow-x-auto">
                <table class="w-full text-sm" id="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Note</th>
                            <th>Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </x-ui.card-content>
        </x-ui.card>
    </div>

    <x-ui.modal id="note-modal">
        <x-ui.dialog-header>
            <x-ui.dialog-title id="note-modal-title">Add note</x-ui.dialog-title>
        </x-ui.dialog-header>
        <form id="note-form" method="post" action="{{ route('notes/save') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="id" id="note-id">
            <div class="space-y-2">
                <x-ui.label for="note-title">Title <span class="text-destructive">*</span></x-ui.label>
                <x-ui.input id="note-title" name="title" required />
            </div>
            <div class="space-y-2">
                <x-ui.label for="note-body">Note</x-ui.label>
                <x-ui.textarea id="note-body" name="note" rows="5" />
            </div>
            <x-ui.dialog-footer>
                <x-ui.button variant="outline" data-modal-dismiss>Cancel</x-ui.button>
                <x-ui.button type="submit">Save</x-ui.button>
            </x-ui.dialog-footer>
        </form>
    </x-ui.modal>
@endsection
@push('scripts')
    <script>
        documentReady(function() {
            app.dataTable('#data-table', {
                url: '{{ route('notes/list') }}',
                columns: [
                    {data: "id", visible: false},
                    {data: "title"},
                    {data: "note", orderable: false, render: function(data) { return $('<div>').text((data || '').substring(0, 80)).html(); }},
                    {data: "updated_at"},
                    {data: "action", orderable: false},
                ],
                order: [[3, "desc"]],
            });

            var $modal = $('#note-modal');

            function openForm(note) {
                $('#note-modal-title').text(note ? 'Edit note' : 'Add note');
                $('#note-id').val(note ? note.id : '');
                $('#note-title').val(note ? note.title : '');
                $('#note-body').val(note ? note.note : '');
                app.openModal($modal);
            }

            $('#note-add').on('click', function() { openForm(null); });
            $(document).on('click', '.note-edit', function() {
                openForm({id: $(this).data('id'), title: $(this).data('title'), note: $(this).data('note')});
            });

            $('#note-form').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form, function(response) {
                        app.ajaxSuccess(response, {next: response.status ? 'table_refresh' : null});
                        if (response.status) {
                            app.closeModal($modal);
                        }
                    });
                },
            });
        });
    </script>
@endpush

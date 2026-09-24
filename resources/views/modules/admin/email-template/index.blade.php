@extends('modules.admin.layouts.main')
@section('title')
    Email Templates
@endsection
@section('content')
    <x-ui.page-header title="Email Templates" :crumbs="[['Dashboard', route('admin/dashboard')], ['Email Templates']]" />

    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title class="text-lg">Email Templates</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content class="overflow-x-auto">
            <table class="w-full text-sm" id="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </x-ui.card-content>
    </x-ui.card>
@endsection
@push('scripts')
    <script>
        documentReady(function() {
            app.dataTable('#data-table', {
                url: '{{ route('admin/email-template/list') }}',
                columns: [
                    {data: "id", visible: false},
                    {data: "title"},
                    {data: "subject"},
                    {data: "updated_at"},
                    {data: "action", orderable: false},
                ],
                order: [[1, "asc"]],
            });
        });
    </script>
@endpush

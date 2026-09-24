@extends('modules.admin.layouts.main')
@section('title')
    Pages
@endsection
@section('content')
    <x-ui.page-header title="Pages" :crumbs="[['Dashboard', route('admin/dashboard')], ['Pages']]" />

    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title class="text-lg">Pages</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content class="overflow-x-auto">
            <table class="w-full text-sm" id="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Status</th>
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
                url: '{{ route('admin/page/list') }}',
                columns: [
                    {data: "id", visible: false},
                    {data: "title"},
                    {data: "slug"},
                    {data: "status", orderable: false},
                    {data: "updated_at"},
                    {data: "action", orderable: false},
                ],
                order: [[4, "desc"]],
            });
        });
    </script>
@endpush

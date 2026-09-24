@extends('modules.admin.layouts.main')
@section('title')
    Blog
@endsection
@section('content')
    @php($viewer = auth()->user())
    <x-ui.page-header title="Blog" :crumbs="[['Dashboard', route('admin/dashboard')], ['Blog']]" />

    <x-ui.card>
        <x-ui.card-header class="!grid-cols-[1fr_auto] items-center">
            <x-ui.card-title class="text-lg">Blog posts</x-ui.card-title>
            @if ($viewer->hasPermission('admin/blog/create'))
                <x-ui.button :href="route('admin/blog/create')" class="pjax"><i class="bx bx-plus"></i> Create</x-ui.button>
            @endif
        </x-ui.card-header>
        <x-ui.card-content class="overflow-x-auto">
            <table class="w-full text-sm" id="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Created</th>
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
                url: '{{ route('admin/blog/list') }}',
                columns: [
                    {data: "id", visible: false},
                    {data: "title"},
                    {data: "category"},
                    {data: "status", orderable: false},
                    {data: "created_at"},
                    {data: "action", orderable: false},
                ],
                order: [[4, "desc"]],
            });
        });
    </script>
@endpush

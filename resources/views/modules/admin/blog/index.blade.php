@extends('modules.admin.layouts.main')
@section('title')
    Blog
@endsection
@section('content')
    @php($viewer = auth()->user())
    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">Blog</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin/dashboard') }}" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Blog</li>
            </ol>
        </nav>
    </div>

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

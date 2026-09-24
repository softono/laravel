@extends('modules.admin.layouts.main')
@section('title')
    Users
@endsection
@section('content')
    @php($sessionUser = auth()->user())

    <!-- Content -->
    <x-ui.page-header title="Users" :crumbs="[['Dashboard', 'admin/dashboard'], ['Users']]" />
    <!-- Users Table -->
    <x-ui.card>
        <x-ui.card-header class="!grid-cols-[1fr_auto] items-center">
            <x-ui.card-title>Users</x-ui.card-title>
            @if ($sessionUser->hasPermission('admin/user/create'))
                <x-ui.button href="admin/user/create" class="pjax">
                    <i class="bx bx-plus"></i>
                    <span>Create</span>
                </x-ui.button>
            @endif
        </x-ui.card-header>
        <x-ui.card-content class="overflow-x-auto">
            <table class="w-full text-sm" id="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Country</th>
                        <th>Status</th>
                        <th>Created At</th>
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
                url: '{{ route('admin/user/list') }}',
                columns: [
                    {data: "id", visible: false},
                    {data: "first_name"},
                    {data: "email"},
                    {data: "phone"},
                    {data: "country"},
                    {data: "status"},
                    {data: "created_at"},
                    {data: "action", orderable: false}
                ],
                order: [6, "desc"],
            });
        });
    </script>
@endpush

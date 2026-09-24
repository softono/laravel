@extends('modules.admin.layouts.main')
@section('title')
    Admin
@endsection
@section('content')
    @php($sessionUser = auth()->user())
    <!-- Content -->

    <x-ui.page-header title="Admin" :crumbs="[['Dashboard', 'admin/dashboard'], ['Admin']]" />

    <!-- Invoice List Table -->
    <x-ui.card>
        <x-ui.card-header class="!grid-cols-[1fr_auto] items-center">
            <x-ui.card-title>Admin</x-ui.card-title>
            @if ($sessionUser->hasPermission('admin/admin/create'))
                <x-ui.button href="admin/admin/create" class="pjax">
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
                        <th>Country</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </x-ui.card-content>
    </x-ui.card>
    <!-- / Content -->
@endsection
@push('scripts')
    <script>
        documentReady(function() {
            app.dataTable('#data-table', {
                url: '{{ route('admin/admin/list') }}',
                columns: [
                    {data: "id", visible: false},
                    {data: "first_name"},
                    {data: "email"},
                    {data: "country"},
                    {data: "phone"},
                    {data: "status"},
                    {data: "action", orderable: false}
                ],
                order: [1, "asc"],
            });
        });
    </script>
@endpush

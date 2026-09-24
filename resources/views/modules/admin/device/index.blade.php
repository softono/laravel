@extends('modules.admin.layouts.main')
@section('title')
    Device
@endsection
@section('content')
    <!-- Content -->

    <x-ui.page-header title="Device" :crumbs="[['Dashboard', 'admin/dashboard'], ['Device']]" />

    <!-- Invoice List Table -->
    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title>Device</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content class="overflow-x-auto">
            <table class="w-full text-sm" id="data-table">
                <thead>
                    <tr>
                        <th>Last Activity</th>
                        <th>Device ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Client</th>
                        <th>IP</th>
                        <th>Location</th>
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
                url: '{{ route('admin/device/list') }}',
                columns: [
                    {data: "last_activity"},
                    {data: "id", visible: false},
                    {data: "first_name", orderable: false},
                    {data: "email"},
                    {data: "client"},
                    {data: "ip"},
                    {data: "location", orderable: false},
                    {data: "action", orderable: false}
                ],
                order: [0, "desc"],
            });
        });
    </script>
@endpush

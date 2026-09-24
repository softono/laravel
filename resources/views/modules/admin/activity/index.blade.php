@extends('modules.admin.layouts.main')
@section('title')
    Log
@endsection
@section('content')
    <!-- Content -->
    <x-ui.page-header title="Log" :crumbs="[['Dashboard', 'admin/dashboard'], ['Log']]" />

    <!-- Invoice List Table -->
    <x-ui.card>
        <x-ui.card-header>
            <x-ui.card-title>Log</x-ui.card-title>
        </x-ui.card-header>
        <x-ui.card-content class="overflow-x-auto">
            <table class="w-full text-sm" id="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Device</th>
                        <th>IP</th>
                        <th>Location</th>
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
                url: '{{ route('admin/activity/list') }}',
                columns: [
                    {data: "created_at"},
                    {data: "type"},
                    {data: "first_name", orderable: false},
                    {data: "email"},
                    {data: "client", orderable: false},
                    {data: "ip"},
                    {data: "location", orderable: false}
                ],
                order: [0, "desc"],
            });
        });
    </script>
@endpush

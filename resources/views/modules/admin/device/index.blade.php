@extends('modules.admin.layouts.main')
@section('title')
    Device
@endsection
@section('content')
    <!-- Content -->

    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">Device</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Device</li>
            </ol>
        </nav>
    </div>

    <!-- Invoice List Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Device</h5>
        </div>
        <div class="card-body overflow-x-auto">
            <table class="w-full text-sm" id="data-table">
                <thead>
                    <tr>
                        <th>Last Activity</th>
                        <th>Device ID</th>
                        <th>Name</th>
                        <th class="text-break">Email</th>
                        <th>Client</th>
                        <th>IP</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

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

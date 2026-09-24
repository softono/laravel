@extends('modules.admin.layouts.main')
@section('title')
    Log
@endsection
@section('content')
    <!-- Content -->
    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">Log</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Log</li>
            </ol>
        </nav>
    </div>

    <!-- Invoice List Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Log</h5>
        </div>
        <div class="card-body overflow-x-auto">
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
        </div>
    </div>
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

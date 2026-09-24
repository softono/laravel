@extends('admin.layouts.main')
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
                        <th>Locaion</th>
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
            datatableObj = $('#data-table').DataTable({
                ajax: dataTableAjax({
                    url: '{{route("admin/user-activity/list")}}',
                    method: 'post'
                }),
                columns: [{
                        data: "created_at",
                        responsivePriority: 5
                    },
                    {
                        data: "type",
                        responsivePriority: 4
                    },
                    {
                        data: "first_name",
                        responsivePriority: 4
                    },
                    {
                        data: "email",
                         class:'wrap-td',
                        responsivePriority: 4
                    },
                    {
                        data: "device",
                        responsivePriority: 4,
                        sortable: false
                    },
                    {
                        data: "ip",
                         class:'wrap-td',
                        responsivePriority: 4,
                        sortable: false
                    },
                    {
                        data: "location",
                        responsivePriority: 3,
                        sortable: false
                    }
                ],
                responsive: true,
                serverSide: true,
                "order": [
                    [0, "desc"]
                ]
            });
        });
    </script>
@endpush

@extends('admin.layouts.main')
@section('title')
    Device
@endsection
@section('content')
    <!-- Content -->


    <div class="breadcrumb-box">
        <h4 class="fw-bold py-3 mb-4">Device</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="admin/dashboard" class="pjax">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Device</li>
            </ol>
        </nav>
    </div>

    <!-- Invoice List Table -->
    <div class="card">
        <div class="card-header justify-content-between">
            <h4 class="align-middle d-sm-inline-block d-none">Device </h4>
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatable-list-table table border-top " id="data-table">
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


    <!--Bootstrap Tables-->
@endsection
@push('scripts')
    <script>
        documentReady(function() {
            datatableObj = $('#data-table').DataTable({
                ajax: dataTableAjax({
                    url: '{{ route('admin/device/list') }}',
                    method: 'post'
                }),
                columns: [{
                        data: "last_activity",
                        responsivePriority: 1
                    },
                    {
                        data: "id",
                        responsivePriority: 6
                    },
                    {
                        data: "first_name",
                        responsivePriority: 2
                    },
                    {
                        data: "email",
                        class: 'wrap-td',
                        responsivePriority: 2
                    },
                    {
                        data: "client",
                        responsivePriority: 4,
                        sortable: false
                    },
                    {
                        data: "ip",
                        class: 'wrap-td',
                        responsivePriority: 3,
                        sortable: false
                    },
                    {
                        data: "location",
                        responsivePriority: 2,
                        sortable: false
                    },
                    {
                        data: "action",
                        responsivePriority: 1,
                        sortable: false
                    },
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

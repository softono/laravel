@extends('admin.layouts.main')
@section('title')
    Admin
@endsection
@section('content')
    <?php $sessionUser = auth()->user(); ?>
    <!-- Content -->

    <div class="breadcrumb-box">
        <h4 class="fw-bold py-3 mb-4">Admin</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <!-- <a href="admin/dashboard">Dashboard</a> -->
                    <a href="admin/dashboard" class="pjax">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Admin</li>
            </ol>
        </nav>
    </div>

    <!-- Invoice List Table -->
    <div class="card">
        <div class="card-header justify-content-between">
            <h4 class="align-middle d-sm-inline-block d-none">Admin</h4>
            @if ($sessionUser->hasPermission('admin/admin/create'))
                <a href="admin/admin/create" class="btn btn-primary d-sm-inline-block d-none pjax"
                    style="float: inline-end;">Create</a>
            @endif
        </div>
        <div class="card-datatable table-responsive">
            <table class="datatable-list-table table border-top" id="data-table">
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
        </div>
    </div>
    <!-- / Content -->

    <!--Bootstrap Tables-->

    <!--Bootstrap Tables-->
@endsection
@push('scripts')
    <script>
        documentReady(function() {
            datatableObj = $('#data-table').DataTable({
                ajax: dataTableAjax({
                    url: '{{ route("admin/admin/list") }}',
                    method: 'post'
                }),
                columns: [{
                        data: "id",
                        responsivePriority: 6
                    }, //,visible:false
                    {
                        data: "first_name",
                        responsivePriority: 4
                    },
                    {
                        data: "email",
                        responsivePriority: 4
                    },
                    {
                        data: "country",
                        responsivePriority: 4
                    },
                    {
                        data: "phone",
                        responsivePriority: 3
                    },
                    {
                        data: "status",
                        responsivePriority: 4
                    },
                    {
                        data: "action",
                        bSortable: false,
                        responsivePriority: 2
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

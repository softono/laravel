@extends('modules.admin.layouts.main')
@section('title')
    Users
@endsection
@section('content')
    <?php $sessionUser = auth()->user(); ?>

    <!-- Content -->
    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">Users</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Users</li>
            </ol>
        </nav>
    </div>
    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Users</h5>
            @if ($sessionUser->hasPermission('admin/user/create'))
                <a href="admin/user/create" class="btn-primary pjax">
                    <i class="bx bx-plus"></i>
                    <span>Create</span>
                </a>
            @endif
        </div>
        <div class="card-body overflow-x-auto">
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
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        documentReady(function() {
            datatableObj = $('#data-table').DataTable({
                ajax: dataTableAjax({
                    url: '{{ route('admin/user/list') }}',
                    method: 'post',
                }),
                columns: [{
                        data: "id",
                        responsivePriority: 4
                    }, //,visible:false
                    {
                        data: "first_name",
                        responsivePriority: 4
                    }, //,visible:false
                    {
                        data: "email",
                        responsivePriority: 2
                    },
                    {
                        data: "phone",
                        responsivePriority: 3
                    },
                    {
                        data: "country",
                        responsivePriority: 4
                    },
                    {
                        data: "status",
                        responsivePriority: 5
                    },
                    {
                        data: "created_at",
                        responsivePriority: 4
                    },
                    {
                        data: "action",
                        bSortable: false,
                        responsivePriority: 1
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

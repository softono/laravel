@extends('admin.layouts.main')
@section('title')
    Users
@endsection
@section('content')
    <?php $sessionUser = auth()->user(); ?>

    <!-- Content -->
    <div class="breadcrumb-box">
        <h4 class="fw-bold py-3 mb-4">Users</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="admin/dashboard" class="pjax">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Users</li>
            </ol>
        </nav>
    </div>
    <!-- Invoice List Table -->
    <div class="card">
        <div class="card-datatable text-nowrap">
            <div id="DataTables_Table_0_wrapper" class="dt-container dt-bootstrap5 dt-empty-footer">
                <div class="row card-header flex-column flex-md-row pb-0">
                    <div
                        class="d-md-flex justify-content-between align-items-center dt-layout-start col-md-auto me-auto mt-0">
                        <h5 class="card-title mb-0 text-md-start text-center">Users</h5>
                    </div>
                    <div
                        class="d-md-flex justify-content-between align-items-center dt-layout-end col-md-auto ms-auto mt-0">
                        <div class="dt-buttons btn-group flex-wrap mb-0">
                            <div class="btn-group">
                                @if ($sessionUser->hasPermission('admin/user/create'))
                                    <a href="admin/user/create" class="btn create-new btn-primary pjax" tabindex="0"
                                        aria-controls="DataTables_Table_0" type="button"><span><span
                                                class="d-flex align-items-center gap-2"><i
                                                    class="icon-base bx bx-plus icon-sm"></i> <span
                                                    class="d-none d-sm-inline-block">Create</span></span></span></a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-datatable table-responsive">
                    <table class="table table-bordered table-responsive" id="data-table">
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
                        data: "status_label",
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

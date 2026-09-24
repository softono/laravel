@extends('admin.layouts.main')
@section('title')
    Email Template
@endsection
@section('content')
    <!-- Content -->

    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">Email Template</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Email Template</li>
            </ol>
        </nav>
    </div>

    <!-- Invoice List Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Email Template</h5>
        </div>
        <div class="card-body overflow-x-auto">
            <table class="w-full text-sm" id="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Subject</th>
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
            datatableObj = $('#data-table').DataTable({
                stateSave: true,
                ajax: dataTableAjax({
                    url: '{{ route('admin/email-template/list') }}',
                    dataSrc: 'data'
                }),
                columns: [{
                        data: "id",
                        responsivePriority: 6
                    }, //,visible:false
                    {
                        data: "title",
                        responsivePriority: 6
                    }, //,visible:false
                    {
                        data: "subject",
                        responsivePriority: 6
                    }, //,visible:false
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

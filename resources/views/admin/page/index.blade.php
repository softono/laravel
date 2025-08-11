@extends('admin.layouts.main')
@section('title')
Pages
@endsection
@section('content')

<!-- Content -->
<div class="breadcrumb-box">
    <h4 class="fw-bold py-3 mb-4">Pages</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="admin/dashboard" class="pjax">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Pages</li>
        </ol>
    </nav>
</div>

<!-- Invoice List Table -->
<div class="card">
    <div class="card-header justify-content-between">
        <h4 class="align-middle d-sm-inline-block d-none">Pages</h4>
    </div>
    <div class="card-datatable table-responsive">
        <table class=" table table-bordered" id="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
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
        stateSave: true,
        ajax: dataTableAjax({
            url: '{{route("admin/page/list")}}',
            method: 'POST'
        }),
        columns: [

            {
                data: "id",
                responsivePriority: 6
            }, //,visible:false
            {
                data: "title",
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
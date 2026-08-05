@extends('admin.layouts.main')
@section('title')
Buckets
@endsection
@section('content')
<div class="breadcrumb-box">
  <h4 class="fw-bold py-3 mb-4">Buckets</h4>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item">
        <a href="admin/dashboard" class="pjax">Dashboard</a>
      </li>
      <li class="breadcrumb-item active">Buckets</li>
    </ol>
  </nav>
</div>

<div class="card">
  <div class="card-header">
    <h4 class="align-middle d-sm-inline-block d-none">All Buckets (read-only)</h4>
  </div>
  <div class="card-datatable table-responsive">
    <table class="datatable-list-table table border-top" id="bucket-data-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Owner</th>
          <th>Visibility</th>
          <th>Objects</th>
          <th>Storage Used</th>
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
  documentReady(function () {
    datatableObj = $('#bucket-data-table').DataTable({
      ajax: dataTableAjax({
        url: '{{ route("admin/bucket/list") }}',
        method: 'post'
      }),
      columns: [
        { data: "id", responsivePriority: 6 },
        { data: "name", responsivePriority: 2 },
        { data: "owner", responsivePriority: 3 },
        { data: "visibility", responsivePriority: 4 },
        { data: "object_count", responsivePriority: 5 },
        { data: "storage_used", responsivePriority: 5 },
        { data: "created_at", responsivePriority: 6 },
        { data: "action", sortable: false, responsivePriority: 1 }
      ],
      responsive: true,
      serverSide: true,
      order: [[0, "desc"]]
    });
  });
</script>
@endpush

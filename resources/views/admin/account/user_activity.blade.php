@extends('admin.layouts.main')
@section('title')
Log
@endsection
@section('content')
<div>
    <!-- Content -->
    <?= view('admin/account/component/account_block'); ?>
    <!-- Ajax Sourced Server-side -->
    <!-- Invoice List Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><span class="font-normal text-slate-500">Activity /</span> List</h5>
        </div>
        <div class="card-body overflow-x-auto">
            <table class="w-full text-sm" id="data-table">
                <thead>
                    <tr>
                        <th>Created At</th>
                        <th>Client</th>
                        <th>Location</th>
                        <th>IP</th>
                        <th>Type</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- / Content -->
@endsection
@push('scripts')
<script>
  documentReady(function() {
    datatableObj = $('#data-table').DataTable({
      ajax: {
        url: '{{route("admin/account/user-activity-list")}}',
        method: 'post',
        dataSrc: 'data',
        data: {
          '_token': CSRF_TOKEN
        },
      },
      columns: [

        {
          data: "created_at",
          responsivePriority: 4
        },
        {
          data: "client",
          responsivePriority: 6
        },
        {
          data: "location",
          responsivePriority: 4,
          sortable: false
        },
        {
          data: "ip",
          responsivePriority: 4
        },
        {
          data: "type",
          responsivePriority: 4
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

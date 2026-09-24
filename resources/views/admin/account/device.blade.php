@extends('admin.layouts.main')
@section('title')
Device
@endsection
@section('content')

<div>
    <?= view('admin/account/component/account_block'); ?>
    <!-- Content -->
    <!-- Invoice List Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><span class="font-normal text-slate-500">Device /</span> List</h5>
        </div>
        <div class="card-body overflow-x-auto">
            <table class="w-full text-sm" id="data-table">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>Client</th>
                        <th>IP</th>
                        <th>Location</th>
                        <th>Last Activity</th>
                        <th>Action</th>
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
      ajax: dataTableAjax({
        url: '{{route("admin/account/device-list")}}',
        method: 'post'
      }),
      columns: [{
          data: "id",
          responsivePriority: 4
        },
        {
          data: "client",
          responsivePriority: 2,
          sortable: false
        },
        {
          data: "ip",
          class:'wrap-td',
          responsivePriority: 2
        },
        {
          data: "location",
          responsivePriority: 2,
          sortable: false
        },
        {
          data: "last_activity",
          responsivePriority: 2
        },
        {
          data: "action",
          responsivePriority: 2,
          sortable: false
        },
      ],
      responsive: true,
      serverSide: true,
      "order": [
        [4, "desc"]
      ]
    });
  });
</script>
@endpush

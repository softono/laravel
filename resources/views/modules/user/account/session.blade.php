@extends($layout)
@section('title')
    Sessions
@endsection
@section('content')
    <div>
        @include('modules.user.account.component.account_block')
        <!-- Invoice List Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><span class="font-normal text-slate-500">Sessions /</span> List</h5>
            </div>
            <div class="card-body overflow-x-auto">
                <table class="w-full text-sm" id="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Client</th>
                            <th>Location</th>
                            <th>Last Activity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        documentReady(function() {
            app.addCSS([
                'https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css',
                'https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css'
            ]);
            app.addJS([
                'https://cdn.datatables.net/2.1.8/js/dataTables.js',
                'https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js',
                'https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js'
            ]);
            datatableObj = $('#data-table').DataTable({
                ajax: dataTableAjax({
                    url: '{{ route($prefix.'account/session-list') }}',
                    method: 'post',
                }),
                columns: [{
                        data: "id",
                        responsivePriority: 4
                    },
                    {
                        data: "client",
                        responsivePriority: 2,
                    },

                    {
                        data: "location",
                        responsivePriority: 2,
                        bSortable: false,
                    },
                    {
                        data: "last_activity",
                        responsivePriority: 2
                    },
                    {
                        data: "action",
                        responsivePriority: 2,
                        orderable: false,
                        render: function(data, type, row) {
                            return data;
                        }
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

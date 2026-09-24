@extends('layouts.main')
@section('title')
    Device
@endsection
@section('content')
    <div>
        {{ view('account/component/account_block', compact('model')) }}
        <!-- Invoice List Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><span class="font-normal text-slate-500">Device /</span> List</h5>
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
        function logoutDevice(id) {
            $.ajax({
                url: '{{ route('account/device-logout') }}',
                type: 'POST',
                data: {
                    id: id,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr) {
                    console.log(xhr.responseText);
                    alert("Something went wrong");
                }
            });
        }
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
                    url: '{{ route('account/device-list') }}',
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

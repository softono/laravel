@extends('layouts.main')
@section('title')
    Log
@endsection
@section('content')
    <!-- Content -->
    <div>
        {{ view('account/component/account_block', compact('model')) }}
        <!-- Ajax Sourced Server-side -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><span class="font-normal text-slate-500">Log /</span> List</h5>
            </div>
            <div class="card-body overflow-x-auto">
                <table class="w-full text-sm" id="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Device</th>
                            <th>Location </th>
                            <th>Type</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <!--/ Ajax Sourced Server-side -->
        <!-- / Content -->
    </div>
@endsection
@push('scripts')
    <script>
        documentReady(function() {
            app.addCSS([
                'https://cdn.datatables.net/2.1.8/css/dataTables.tailwindcss.css',
                'https://cdn.datatables.net/responsive/3.0.4/css/responsive.dataTables.css'
            ])
            app.addJS([
                'https://cdn.datatables.net/2.1.8/js/dataTables.js',
                'https://cdn.datatables.net/responsive/3.0.4/js/dataTables.responsive.js',
                'https://cdn.datatables.net/2.1.8/js/dataTables.tailwindcss.js'
            ]);
            datatableObj = $('#data-table').DataTable({
                ajax: dataTableAjax({
                    url: '{{ route('account/user-activity-list') }}',
                    method: 'post',
                }),
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
                        orderable: false,
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

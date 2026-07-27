@extends('layouts.main')
@section('title')
    Log
@endsection
@section('content')
    <!-- Content -->
    <div class="row">
        <div class="col-md-12">
            {{ view('account/component/account_block', compact('model')) }}
            {{-- <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Log /</span> List</h4> --}}
            <!-- Ajax Sourced Server-side -->
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-1"><span class="text-muted fw-light">Log /</span> List</h5>
                </div>
                <div class="card-datatable table-responsive">
                    <table class="datatable-list-table table border-top" id="data-table">
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
        </div>
        <!--/ Ajax Sourced Server-side -->
        <!-- / Content -->
        <div class="content-backdrop fade"></div>
        <!--Bootstrap Tables-->
    </div>
    </div>
@endsection
@push('scripts')
    <script>
        documentReady(function() {
            app.addCSS([
                'theme/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css',
                'theme/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css'
            ])
            app.addJS(['theme/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js']);
            datatableObj = $('#data-table').DataTable({
                ajax: {
                    url: '{{ route('account/user-activity-list') }}',
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

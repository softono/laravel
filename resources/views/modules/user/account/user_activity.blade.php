@extends($layout)
@section('title')
    Log
@endsection
@section('content')
    <!-- Content -->
    <div>
        @include('modules.user.account.component.account_block')
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
            app.dataTable('#data-table', {
                url: '{{ route($prefix.'account/user-activity-list') }}',
                columns: [
                    {data: "created_at"},
                    {data: "client", orderable: false},
                    {data: "location", orderable: false},
                    {data: "ip"},
                    {data: "type"}
                ],
                order: [0, "desc"],
            });
        });
    </script>
@endpush

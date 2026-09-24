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
            app.dataTable('#data-table', {
                url: '{{ route($prefix.'account/session-list') }}',
                columns: [
                    {data: "id", visible: false},
                    {data: "client"},
                    {data: "location", orderable: false},
                    {data: "last_activity"},
                    {data: "action", orderable: false}
                ],
                order: [3, "desc"],
            });
        });
    </script>
@endpush

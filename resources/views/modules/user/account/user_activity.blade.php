@extends($layout)
@section('title')
    Log
@endsection
@section('content')
    <!-- Content -->
    <div>
        @include('modules.user.account.component.account_block')
        <!-- Ajax Sourced Server-side -->
        <x-ui.card>
            <x-ui.card-header>
                <x-ui.card-title><span class="font-normal text-muted-foreground">Log /</span> List</x-ui.card-title>
            </x-ui.card-header>
            <x-ui.card-content class="overflow-x-auto">
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
            </x-ui.card-content>
        </x-ui.card>
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

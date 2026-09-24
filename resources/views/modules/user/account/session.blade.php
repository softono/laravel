@extends($layout)
@section('title')
    Sessions
@endsection
@section('content')
    <div>
        @include('modules.user.account.component.account_block')
        <!-- Invoice List Table -->
        <x-ui.card>
            <x-ui.card-header class="!grid-cols-[1fr_auto] items-center">
                <x-ui.card-title><span class="font-normal text-muted-foreground">Sessions /</span> List</x-ui.card-title>
                <x-ui.button variant="outline" type="button" onclick="app.confirmAction(this);"
                    data-action="{{ route($prefix.'account/session-logout-others') }}" data-next="table_refresh">
                    Sign out other devices
                </x-ui.button>
            </x-ui.card-header>
            <x-ui.card-content class="overflow-x-auto">
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
            </x-ui.card-content>
        </x-ui.card>
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

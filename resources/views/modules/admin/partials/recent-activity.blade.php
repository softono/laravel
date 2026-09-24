<x-ui.card>
    <x-ui.card-header>
        <x-ui.card-title>Recent Activity</x-ui.card-title>
    </x-ui.card-header>
    <x-ui.card-content>
        <x-ui.table>
            <thead class="[&_tr]:border-b">
                <x-ui.tr>
                    <x-ui.th>Type</x-ui.th>
                    <x-ui.th>Client</x-ui.th>
                    <x-ui.th>IP</x-ui.th>
                    <x-ui.th>Date</x-ui.th>
                </x-ui.tr>
            </thead>
            <tbody>
                @forelse ($activities as $activity)
                    <x-ui.tr>
                        <x-ui.td>{{ \App\Constants\UserActivity::label($activity->type ?? '') }}</x-ui.td>
                        <x-ui.td>{{ \App\Helpers\ClientInfo::deviceNameFor($activity->client) }}</x-ui.td>
                        <x-ui.td>{{ $activity->ip }}</x-ui.td>
                        <x-ui.td>{{ $general->dateFormat($activity->created_at) }}</x-ui.td>
                    </x-ui.tr>
                @empty
                    <x-ui.tr>
                        <x-ui.td colspan="4" class="text-muted-foreground text-center">No activity.</x-ui.td>
                    </x-ui.tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card-content>
</x-ui.card>

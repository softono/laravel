<x-ui.card>
    <x-ui.card-header>
        <x-ui.card-title>Recent Sessions</x-ui.card-title>
    </x-ui.card-header>
    <x-ui.card-content>
        <x-ui.table>
            <thead class="[&_tr]:border-b">
                <x-ui.tr>
                    <x-ui.th>Client</x-ui.th>
                    <x-ui.th>IP</x-ui.th>
                    <x-ui.th>Last Activity</x-ui.th>
                </x-ui.tr>
            </thead>
            <tbody>
                @forelse ($sessions as $session)
                    <x-ui.tr>
                        <x-ui.td>{{ \App\Helpers\ClientInfo::deviceNameFor($session->user_agent) }}</x-ui.td>
                        <x-ui.td>{{ $session->ip_address }}</x-ui.td>
                        <x-ui.td>{{ $general->dateFormat($session->updated_at) }}</x-ui.td>
                    </x-ui.tr>
                @empty
                    <x-ui.tr>
                        <x-ui.td colspan="3" class="text-muted-foreground text-center">No sessions.</x-ui.td>
                    </x-ui.tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card-content>
</x-ui.card>

<x-ui.card>
    <x-ui.card-header>
        <x-ui.card-title>Sent Mails</x-ui.card-title>
    </x-ui.card-header>
    <x-ui.card-content>
        <x-ui.table>
            <thead class="[&_tr]:border-b">
                <x-ui.tr>
                    <x-ui.th>Subject</x-ui.th>
                    <x-ui.th>Message</x-ui.th>
                    <x-ui.th>Sent At</x-ui.th>
                </x-ui.tr>
            </thead>
            <tbody>
                @forelse ($mails as $mail)
                    <x-ui.tr>
                        <x-ui.td>{{ $mail->subject }}</x-ui.td>
                        <x-ui.td>{{ \Illuminate\Support\Str::limit($mail->message, 60) }}</x-ui.td>
                        <x-ui.td>{{ $general->dateFormat($mail->created_at) }}</x-ui.td>
                    </x-ui.tr>
                @empty
                    <x-ui.tr>
                        <x-ui.td colspan="3" class="text-muted-foreground text-center">No sent emails.</x-ui.td>
                    </x-ui.tr>
                @endforelse
            </tbody>
        </x-ui.table>
    </x-ui.card-content>
</x-ui.card>

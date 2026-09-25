@extends($layout)
@section('title', 'Two-Factor Authentication')
@section('content')
    <div>
        @include('modules.user.account.component.account_block')
        <div class="space-y-4">
            <x-ui.card>
                <x-ui.card-header>
                    <x-ui.card-title>Two-Factor Authentication</x-ui.card-title>
                </x-ui.card-header>
                <x-ui.card-content>
                    <div id="tfa-status-block">
                        @if ($tfaStatus['enabled'] ?? false)
                            <p class="mb-0 text-success">Two-factor authentication is enabled.
                                @if (!empty($tfaStatus['backup_codes_remaining']))
                                    ({{ $tfaStatus['backup_codes_remaining'] }} backup codes remaining)
                                @endif
                            </p>
                        @else
                            <p class="mb-0 text-muted-foreground">Two-factor authentication is disabled.</p>
                        @endif
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <x-ui.card id="tfa-setup-card" style="display:none;">
                <x-ui.card-header>
                    <x-ui.card-title>Set Up Authenticator App</x-ui.card-title>
                </x-ui.card-header>
                <x-ui.card-content>
                    <div id="tfa-qr-container" class="mb-3"></div>
                    <p class="mb-1"><strong>Secret key</strong> (if you can't scan the QR code):</p>
                    <p class="mb-3"><code id="tfa-secret"></code></p>

                    <div id="tfa-backup-codes-container" class="mb-4" style="display:none;">
                        <p class="mb-2"><strong>Save these backup codes.</strong> Each can be used once if you lose access to your authenticator app.</p>
                        <div class="grid grid-cols-2 gap-2" id="tfa-backup-codes-grid"></div>
                    </div>

                    <div class="mb-3" style="max-width: 320px;">
                        <x-ui.label for="tfa-setup-code" class="mb-2">Enter the 6-digit code from your app to confirm setup</x-ui.label>
                        <x-ui.input type="text" id="tfa-setup-code" maxlength="6" placeholder="000000" />
                    </div>
                    <x-ui.button id="tfa-confirm-setup" type="submit">Confirm and Enable</x-ui.button>
                </x-ui.card-content>
            </x-ui.card>

            <x-ui.card id="tfa-manage-card" style="{{ ($tfaStatus['enabled'] ?? false) ? '' : 'display:none;' }}">
                <x-ui.card-header>
                    <x-ui.card-title>Manage</x-ui.card-title>
                </x-ui.card-header>
                <x-ui.card-content>
                    <div class="mb-3">
                        <x-ui.label for="tfa-current-password" class="mb-2">Current Password</x-ui.label>
                        <x-ui.input type="password" id="tfa-current-password" style="max-width:320px;" />
                    </div>
                    <x-ui.button variant="outline" class="mr-2" id="tfa-regenerate-codes" type="submit">Regenerate Backup Codes</x-ui.button>
                    <x-ui.button variant="destructive" id="tfa-disable" type="submit">Disable Two-Factor Authentication</x-ui.button>
                </x-ui.card-content>
            </x-ui.card>

            <x-ui.card id="tfa-enable-card" style="{{ ($tfaStatus['enabled'] ?? false) ? 'display:none;' : '' }}">
                <x-ui.card-header>
                    <x-ui.card-title>Enable Two-Factor Authentication</x-ui.card-title>
                </x-ui.card-header>
                <x-ui.card-content>
                    <div class="mb-3">
                        <x-ui.label for="tfa-enable-password" class="mb-2">Current Password</x-ui.label>
                        <x-ui.input type="password" id="tfa-enable-password" style="max-width:320px;" />
                    </div>
                    <x-ui.button id="tfa-start-setup" type="submit">Start Setup</x-ui.button>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ $general->assetUrl('assets/js/account/tfa.js') }}"></script>
    <script>
        accountTfa.init({
            statusUrl: '{{ url('/auth/2fa/status') }}',
            enableUrl: '{{ url('/auth/2fa/enable') }}',
            verifySetupUrl: '{{ url('/auth/2fa/verify-setup') }}',
            disableUrl: '{{ url('/auth/2fa/disable') }}',
            backupCodesUrl: '{{ url('/auth/2fa/backup-codes') }}',
        });
    </script>
@endpush

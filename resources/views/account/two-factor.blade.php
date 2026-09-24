@extends('layouts.main')
@section('title', 'Two-Factor Authentication')
@section('content')
    <div>
        {{ view('account/component/account_block', compact('model')) }}
        <div class="space-y-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Two-Factor Authentication</h5>
                    <div id="tfa-status-block">
                        @if ($tfaStatus['enabled'] ?? false)
                            <p class="mb-0 text-emerald-600">Two-factor authentication is enabled.
                                @if (!empty($tfaStatus['backup_codes_remaining']))
                                    ({{ $tfaStatus['backup_codes_remaining'] }} backup codes remaining)
                                @endif
                            </p>
                        @else
                            <p class="mb-0 text-slate-500">Two-factor authentication is disabled.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card" id="tfa-setup-card" style="display:none;">
                <div class="card-header">
                    <h5 class="card-title mb-4">Set Up Authenticator App</h5>
                    <div id="tfa-qr-container" class="mb-3"></div>
                    <p class="mb-1"><strong>Secret key</strong> (if you can't scan the QR code):</p>
                    <p class="mb-3"><code id="tfa-secret"></code></p>

                    <div id="tfa-backup-codes-container" class="mb-4" style="display:none;">
                        <p class="mb-2"><strong>Save these backup codes.</strong> Each can be used once if you lose access to your authenticator app.</p>
                        <div class="grid grid-cols-2 gap-2" id="tfa-backup-codes-grid"></div>
                    </div>

                    <div class="mb-3" style="max-width: 320px;">
                        <label for="tfa-setup-code" class="form-label">Enter the 6-digit code from your app to confirm setup</label>
                        <input type="text" class="form-input" id="tfa-setup-code" maxlength="6" placeholder="000000" />
                    </div>
                    <button class="btn-primary" id="tfa-confirm-setup">Confirm and Enable</button>
                </div>
            </div>

            <div class="card" id="tfa-manage-card" style="{{ ($tfaStatus['enabled'] ?? false) ? '' : 'display:none;' }}">
                <div class="card-header">
                    <h5 class="card-title mb-4">Manage</h5>
                    <div class="mb-3">
                        <label for="tfa-current-password" class="form-label">Current Password</label>
                        <input type="password" class="form-input" id="tfa-current-password" style="max-width:320px;" />
                    </div>
                    <button class="btn-outline mr-2" id="tfa-regenerate-codes">Regenerate Backup Codes</button>
                    <button class="btn-danger" id="tfa-disable">Disable Two-Factor Authentication</button>
                </div>
            </div>

            <div class="card" id="tfa-enable-card" style="{{ ($tfaStatus['enabled'] ?? false) ? 'display:none;' : '' }}">
                <div class="card-header">
                    <h5 class="card-title mb-4">Enable Two-Factor Authentication</h5>
                    <div class="mb-3">
                        <label for="tfa-enable-password" class="form-label">Current Password</label>
                        <input type="password" class="form-input" id="tfa-enable-password" style="max-width:320px;" />
                    </div>
                    <button class="btn-primary" id="tfa-start-setup">Start Setup</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/account/tfa.js') }}"></script>
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

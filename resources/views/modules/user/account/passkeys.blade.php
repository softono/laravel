@extends('layouts.main')
@section('title', 'Passkeys')
@section('content')
    <div>
        {{ view('modules.user.account.component.account_block', compact('model')) }}
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-2">Passkeys</h5>
                <p class="mb-4 text-sm text-slate-500">Sign in without a password using your device's fingerprint, face, or security key.</p>

                <div id="passkey-unsupported" class="alert-warning" style="display:none;">
                    Passkeys are not supported in this browser.
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="passkey-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Added</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="passkey-list"></tbody>
                    </table>
                </div>

                <div class="mt-3 flex items-center gap-2">
                    <input type="text" class="form-input" id="passkey-name" placeholder="Name this passkey (e.g. My Phone)" style="max-width:280px;" />
                    <button class="btn-primary" id="passkey-add">Add a Passkey</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/auth/passkey.js') }}"></script>
    <script src="{{ asset('assets/js/account/passkeys.js') }}"></script>
    <script>
        accountPasskeys.init({
            listUrl: '{{ url('/auth/passkey/list') }}',
            registerOptionsUrl: '{{ url('/auth/passkey/register-options') }}',
            registerVerifyUrl: '{{ url('/auth/passkey/register-verify') }}',
            deleteUrl: '{{ url('/auth/passkey/delete') }}',
        });
    </script>
@endpush

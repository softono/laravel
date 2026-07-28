@extends('layouts.main')
@section('title', 'Passkeys')
@section('content')
    <div class="row">
        <div class="col-md-12">
            {{ view('account/component/account_block', compact('model')) }}
            <div class="main-card mb-3 card">
                <div class="card-header">
                    <h5 class="mb-2">Passkeys</h5>
                    <p class="text-muted mb-4">Sign in without a password using your device's fingerprint, face, or security key.</p>

                    <div id="passkey-unsupported" class="alert alert-warning" style="display:none;">
                        Passkeys are not supported in this browser.
                    </div>

                    <table class="table" id="passkey-table">
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

                    <div class="d-flex gap-2 align-items-center mt-3">
                        <input type="text" class="form-control" id="passkey-name" placeholder="Name this passkey (e.g. My Phone)" style="max-width:280px;" />
                        <button class="btn btn-primary" id="passkey-add">Add a Passkey</button>
                    </div>
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
            listUrl: '{{ url('/api/auth/passkey/list') }}',
            registerOptionsUrl: '{{ url('/api/auth/passkey/register-options') }}',
            registerVerifyUrl: '{{ url('/api/auth/passkey/register-verify') }}',
            deleteUrl: '{{ url('/api/auth/passkey/delete') }}',
        });
    </script>
@endpush

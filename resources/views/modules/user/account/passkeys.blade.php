@extends($layout)
@section('title', 'Passkeys')
@section('content')
    <div>
        @include('modules.user.account.component.account_block')
        <x-ui.card>
            <x-ui.card-header>
                <x-ui.card-title>Passkeys</x-ui.card-title>
                <x-ui.card-description>Sign in without a password using your device's fingerprint, face, or security key.</x-ui.card-description>
            </x-ui.card-header>
            <x-ui.card-content>
                <x-ui.alert variant="warning" id="passkey-unsupported" style="display:none;">
                    <x-ui.alert-description>Passkeys are not supported in this browser.</x-ui.alert-description>
                </x-ui.alert>

                <x-ui.table id="passkey-table">
                    <thead>
                        <x-ui.tr>
                            <x-ui.th>Name</x-ui.th>
                            <x-ui.th>Type</x-ui.th>
                            <x-ui.th>Added</x-ui.th>
                            <x-ui.th></x-ui.th>
                        </x-ui.tr>
                    </thead>
                    <tbody id="passkey-list"></tbody>
                </x-ui.table>

                <div class="mt-4 flex items-center gap-2">
                    <x-ui.input type="text" id="passkey-name" placeholder="Name this passkey (e.g. My Phone)" class="max-w-70" />
                    <x-ui.button id="passkey-add" type="button">Add a Passkey</x-ui.button>
                </div>
            </x-ui.card-content>
        </x-ui.card>
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

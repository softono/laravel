@extends('layouts.blank')
@section('title')
    Approve Login
@endsection
@section('content')
    <div class="w-full max-w-md">
        <x-ui.card>
            <x-ui.card-content>
                <div class="mb-6 flex justify-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                            class="h-10 w-10 rounded-full object-cover" alt="">
                        <span class="text-lg font-bold text-foreground">{{ config('setting.app_name') }}</span>
                    </a>
                </div>

                <div id="approve-loading" class="py-4 text-center">
                    <span class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-muted border-t-primary"></span>
                </div>

                <div id="approve-content" style="display:none;" class="text-center">
                    <h4 class="mb-1 text-xl font-semibold text-foreground">Confirm Login</h4>
                    <p class="mb-3">Someone is trying to log in as <strong id="approve-email"></strong> on:</p>
                    <p class="mb-3"><strong id="approve-device"></strong></p>
                    <p class="mb-1">Confirm this code matches the one shown on the other device:</p>
                    <p class="mb-4">
                        <span id="approve-code" class="bg-muted inline-block rounded-full px-5 py-1.5 font-mono text-2xl tracking-[0.2em]"></span>
                    </p>
                    <div class="flex justify-center gap-2">
                        <x-ui.button type="button" id="approve-btn">Approve</x-ui.button>
                        <x-ui.button variant="outline" type="button" class="text-destructive border-destructive/40 hover:bg-destructive/10" id="reject-btn">Reject</x-ui.button>
                    </div>
                </div>

                <div id="approve-result" style="display:none;" class="py-4 text-center">
                    <p id="approve-result-message" class="mb-0"></p>
                </div>

                <div id="approve-invalid" style="display:none;" class="py-4 text-center">
                    <p class="mb-0 text-destructive">This login link is no longer valid.</p>
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>
@endsection
@push('scripts')
    <script src="{{ $general->assetUrl('assets/js/auth/approve.js') }}"></script>
    <script>
        loginApprove.init({
            infoUrl: '{{ url('/auth/login-link/approve') }}',
            id: '{{ request('id') }}',
            token: '{{ request('token') }}',
        });
    </script>
@endpush

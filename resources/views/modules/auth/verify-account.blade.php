@extends('layouts.blank')
@section('title')
    Verify Your Account
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
                <h4 class="mb-1 text-xl font-semibold text-foreground">Verify Your Account</h4>
                <p class="mb-6 text-sm text-muted-foreground">We've sent a 6-digit code to your email. Enter it below to verify your account.</p>

                <form id="verify-form" class="mb-4" action="{{ url('/auth/verify-account') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <x-ui.label for="email" class="mb-2">Email <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input type="email" id="email" name="email"
                            value="{{ $email }}" placeholder="Enter your email" :readonly="(bool) $email" />
                    </div>
                    <div class="mb-6">
                        <x-ui.label for="otp" class="mb-2">OTP <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input type="text" id="otp" name="otp" maxlength="6"
                            inputmode="numeric" placeholder="Enter the 6-digit OTP" autofocus />
                    </div>
                    <x-ui.button class="w-full mb-4" type="submit" id="verify-submit">Verify</x-ui.button>
                </form>

                <p class="text-center">
                    <button type="button" class="text-sm text-primary hover:underline" id="resend-otp" data-resend-seconds="60">
                        Resend OTP
                    </button>
                </p>
            </x-ui.card-content>
        </x-ui.card>
    </div>
@endsection
@push('scripts')
    <script src="{{ $general->assetUrl('assets/js/auth/countdown.js') }}"></script>
    <script>
        document.getElementById('verify-form').addEventListener('submit', function (event) {
            event.preventDefault();

            var btn = document.getElementById('verify-submit');
            btn.disabled = true;

            app.ajaxForm(this, function (response) {
                btn.disabled = false;
                app.showMessage(response.message, response.status ? 'success' : 'error');

                if (response.status == 1) {
                    window.location.href = '{{ url('/login') }}';
                }
            });
        });

        authCountdown.attachResend(document.getElementById('resend-otp'), function () {
            app.ajaxPost('{{ url('/auth/otp') }}', {
                email: document.getElementById('email').value,
                purpose: 'verify'
            }, function (response) {
                app.showMessage(response.message, response.status ? 'success' : 'error');
            });
        });
    </script>
@endpush

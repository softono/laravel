@extends('layouts.blank')
@section('title')
    Login To Your Account
@endsection
@section('content')
    <div class="w-full max-w-md">
        <x-ui.card>
            <x-ui.card-content>
                <!-- Logo -->
                <div class="mb-6 flex justify-center">
                    <a href="/" class="flex items-center gap-2">
                        <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                            class="h-10 w-10 rounded-full object-cover" alt="">
                        <span class="text-lg font-bold text-foreground">{{ config('setting.app_name') }}</span>
                    </a>
                </div>
                <!-- /Logo -->
                <h4 class="mb-1 text-xl font-semibold text-foreground">Welcome to {{ config('setting.app_name') }} 👋</h4>
                <p class="mb-6 text-sm text-muted-foreground">Please Log-in to your account</p>

                <form id="login-form" class="mb-4" action="{{ url('/auth/login') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <x-ui.label for="login-email" class="mb-2">Email <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input type="email" id="login-email" name="email"
                            placeholder="Enter your email" autocomplete="username webauthn" autofocus />
                        <label id="email-error" class="error" for="login-email" style="display:none;"></label>
                    </div>
                    <div class="mb-6">
                        <x-ui.label class="mb-2" for="password">Password <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.password-input id="password" name="password"
                                autocomplete="current-password" aria-describedby="password" />
                        <label id="password-error" class="error" for="password" style="display:none;"></label>
                    </div>
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-ui.checkbox id="remember-me" name="remember"
                                    value="1" />
                                <x-ui.label class="font-normal" for="remember-me">Remember Me</x-ui.label>
                            </div>
                            <a href="{{ url('/password-forgot') }}" class="text-sm text-primary hover:underline">
                                Forgot Password?
                            </a>
                        </div>
                    </div>

                    {{-- Shown by the script once the server asks for it after repeated failures. --}}
                    <div id="login-captcha" style="display:none;">
                        @include('common.recaptcha')
                    </div>

                    <div class="mb-6">
                        <x-ui.button class="w-full" type="submit" id="login-submit">Login</x-ui.button>
                    </div>
                </form>

                <x-ui.button variant="outline" type="button" class="w-full mb-4" id="passkey-login-btn"
                    data-options-url="{{ url('/auth/passkey/login-options') }}"
                    data-verify-url="{{ url('/auth/passkey/login-verify') }}"
                    data-dashboard-url="{{ url($redirectPath) }}">
                    Sign in with a passkey
                </x-ui.button>

                <x-ui.button variant="outline" type="button" class="w-full mb-4" id="magic-link-toggle"
                    data-start-url="{{ url('/auth/login-link') }}"
                    data-poll-url="{{ url('/auth/login-link/poll') }}"
                    data-dashboard-url="{{ url($redirectPath) }}">
                    Login with Magic Link
                </x-ui.button>

                @if (config('setting.user_login_with_otp') == 1)
                    <x-ui.button variant="outline" type="button" class="w-full mb-4" id="otp-toggle"
                        data-url="{{ url('/auth/login-otp') }}"
                        data-captcha="{{ config('setting.google_recaptcha') ? 1 : 0 }}"
                        data-tfa-url="{{ url('/verify') }}?type=tfa&redirect={{ urlencode($redirectPath) }}"
                        data-verify-account-url="{{ url('/verify-account') }}"
                        data-dashboard-url="{{ url($redirectPath) }}">
                        Login with OTP
                    </x-ui.button>
                    <div id="otp-panel" style="display:none;" class="mb-4">
                        <div id="otp-start">
                            <div class="mb-4">
                                <x-ui.label for="otp-email" class="mb-2">Email</x-ui.label>
                                <x-ui.input type="email" id="otp-email" placeholder="Enter your email" />
                            </div>
                            <x-ui.button type="button" class="w-full" id="otp-send">Send OTP</x-ui.button>
                        </div>
                        <div id="otp-verify" style="display:none;">
                            <div class="mb-4">
                                <x-ui.label for="otp-code" class="mb-2">OTP</x-ui.label>
                                <x-ui.input type="text" id="otp-code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" placeholder="6-digit code" />
                            </div>
                            <x-ui.button type="button" class="w-full mb-2" id="otp-submit">Login</x-ui.button>
                            <x-ui.button variant="outline" type="button" class="w-full" id="otp-resend">Resend OTP</x-ui.button>
                        </div>
                    </div>
                @endif

                @if (config('services.google.client_id'))
                    <x-ui.button variant="outline" href="{{ url('/auth/google') }}" class="w-full mb-4 inline-flex items-center justify-center">
                        Sign in with Google
                    </x-ui.button>
                @endif

                <div id="magic-link-panel" style="display:none;" class="mb-4">
                    <div id="magic-link-start">
                        <div class="mb-4">
                            <x-ui.label for="magic-link-email" class="mb-2">Email</x-ui.label>
                            <x-ui.input type="email" id="magic-link-email" placeholder="Enter your email" />
                        </div>
                        <x-ui.button type="button" class="w-full" id="magic-link-send">Send Login Link</x-ui.button>
                    </div>
                    <div id="magic-link-waiting" style="display:none;" class="text-center">
                        <div class="mb-3 flex justify-center">
                            <span class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-muted border-t-primary"></span>
                        </div>
                        <p class="mb-2">We sent a login link to your email. Open it on any device to continue.</p>
                        <p class="mb-1">Confirm this code matches:</p>
                        <p class="mb-3">
                            <span id="magic-link-code" class="bg-muted inline-block rounded-full px-5 py-1.5 font-mono text-2xl tracking-[0.2em]"></span>
                        </p>
                        <p id="magic-link-status" class="text-destructive mb-0" style="display:none;"></p>
                    </div>
                </div>

                <p class="text-center text-sm text-muted-foreground">
                    <span>New on our platform?</span>
                    <a href="{{ url('/register') }}" class="text-primary hover:underline">
                        <span>Create an account</span>
                    </a>
                </p>
            </x-ui.card-content>
        </x-ui.card>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/auth/login-link.js') }}"></script>
    <script src="{{ asset('assets/js/auth/login-otp.js') }}"></script>
    <script src="{{ asset('assets/js/auth/passkey.js') }}"></script>
    <script>
        (function () {
            var btn = document.getElementById('passkey-login-btn');
            if (!btn) { return; }
            if (!passkeyAuth.isSupported()) { btn.style.display = 'none'; return; }
            btn.addEventListener('click', function () {
                btn.disabled = true;
                passkeyAuth.login(btn.dataset.optionsUrl, btn.dataset.verifyUrl, function (response) {
                    btn.disabled = false;
                    if (response.status == 1) { window.location.href = btn.dataset.dashboardUrl; }
                    else { app.showMessage(response.message, 'error'); }
                });
            });
        })();
    </script>
    <script>
        document.getElementById('login-form').addEventListener('submit', function (event) {
            event.preventDefault();
            var email = document.getElementById('login-email');
            var password = document.getElementById('password');
            if (!email.value) { app.showMessage('Please enter your email.', 'error'); return; }
            if (!password.value) { app.showMessage('Please enter your password.', 'error'); return; }
            app.ajaxForm(this, function (response) {
                if (response.status == 1) {
                    if (response.data && response.data.requires_tfa) { window.location.href = '{{ url('/verify') }}?type=tfa&redirect=' + encodeURIComponent(@json($redirectPath)); return; }
                    window.location.href = @json(url($redirectPath));
                } else {
                    var data = response.data || {};
                    if (data.requires_captcha) {
                        document.getElementById('login-captcha').style.display = 'block';
                    }
                    if (data.requires_verification) {
                        window.location.href = '{{ url('/verify-account') }}?code=' + btoa(data.email || email.value);
                        return;
                    }
                    app.showMessage(response.message, 'error');
                }
            });
        });
    </script>
@endpush

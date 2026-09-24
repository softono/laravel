@extends('layouts.blank')
@section('title')
    Login To Your Account
@endsection
@section('content')
    <div class="w-full max-w-md">
        <div class="card">
            <div class="card-body p-6 sm:p-8">
                <!-- Logo -->
                <div class="mb-6 flex justify-center">
                    <a href="/" class="flex items-center gap-2">
                        <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                            class="h-10 w-10 rounded-full object-cover" alt="">
                        <span class="text-lg font-bold text-slate-800">{{ config('setting.app_name') }}</span>
                    </a>
                </div>
                <!-- /Logo -->
                <h4 class="mb-1 text-xl font-semibold text-slate-800">Welcome to {{ config('setting.app_name') }} 👋</h4>
                <p class="mb-6 text-sm text-slate-500">Please Log-in to your account</p>

                <form id="login-form" class="mb-4" action="{{ url('/auth/login') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="login-email" class="form-label">Email <span class="text-rose-600">*</span></label>
                        <input type="email" class="form-input" id="login-email" name="email"
                            placeholder="Enter your email" autocomplete="username webauthn" autofocus />
                        <label id="email-error" class="error" for="login-email" style="display:none;"></label>
                    </div>
                    <div class="mb-6">
                        <label class="form-label" for="password">Password <span class="text-rose-600">*</span></label>
                        <div class="input-group">
                            <input type="password" id="password" class="form-input" name="password"
                                autocomplete="current-password" aria-describedby="password" />
                            <span class="input-group-text cursor-pointer" data-password-toggle="#password">
                                <i class="bx bx-hide"></i>
                            </span>
                        </div>
                        <label id="password-error" class="error" for="password" style="display:none;"></label>
                    </div>
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <input class="form-check-input" type="checkbox" id="remember-me" name="remember"
                                    value="1" />
                                <label class="form-check-label" for="remember-me">Remember Me</label>
                            </div>
                            <a href="{{ url('/password-forgot') }}" class="text-sm text-primary-600 hover:underline">
                                Forgot Password?
                            </a>
                        </div>
                    </div>

                    {{-- Shown by the script once the server asks for it after repeated failures. --}}
                    <div id="login-captcha" style="display:none;">
                        @include('common.recaptcha')
                    </div>

                    <div class="mb-6">
                        <button class="btn-primary w-full" type="submit" id="login-submit">Login</button>
                    </div>
                </form>

                <button type="button" class="btn-outline w-full mb-4" id="passkey-login-btn"
                    data-options-url="{{ url('/auth/passkey/login-options') }}"
                    data-verify-url="{{ url('/auth/passkey/login-verify') }}"
                    data-dashboard-url="{{ url($redirectPath) }}">
                    Sign in with a passkey
                </button>

                <button type="button" class="btn-outline w-full mb-4" id="magic-link-toggle"
                    data-start-url="{{ url('/auth/login-link') }}"
                    data-poll-url="{{ url('/auth/login-link/poll') }}"
                    data-dashboard-url="{{ url($redirectPath) }}">
                    Login with Magic Link
                </button>

                @if (config('services.google.client_id'))
                    <a href="{{ url('/auth/google') }}" class="btn-outline w-full mb-4 inline-flex items-center justify-center">
                        Sign in with Google
                    </a>
                @endif

                <div id="magic-link-panel" style="display:none;" class="mb-4">
                    <div id="magic-link-start">
                        <div class="mb-4">
                            <label for="magic-link-email" class="form-label">Email</label>
                            <input type="email" class="form-input" id="magic-link-email" placeholder="Enter your email" />
                        </div>
                        <button type="button" class="btn-primary w-full" id="magic-link-send">Send Login Link</button>
                    </div>
                    <div id="magic-link-waiting" style="display:none;" class="text-center">
                        <div class="mb-3 flex justify-center">
                            <span class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600"></span>
                        </div>
                        <p class="mb-2">We sent a login link to your email. Open it on any device to continue.</p>
                        <p class="mb-1">Confirm this code matches:</p>
                        <p class="mb-3">
                            <span id="magic-link-code" style="font-family:monospace;font-size:1.5rem;letter-spacing:0.2em;background:#f5f5f5;border-radius:999px;padding:0.4rem 1.2rem;display:inline-block;"></span>
                        </p>
                        <p id="magic-link-status" class="text-rose-600 mb-0" style="display:none;"></p>
                    </div>
                </div>

                <p class="text-center text-sm text-slate-500">
                    <span>New on our platform?</span>
                    <a href="{{ url('/register') }}" class="text-primary-600 hover:underline">
                        <span>Create an account</span>
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/auth/password-toggle.js') }}"></script>
    <script src="{{ asset('assets/js/auth/login-link.js') }}"></script>
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

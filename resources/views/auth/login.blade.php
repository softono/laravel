@extends('layouts.blank')
@section('title')
    Login To Your Account
@endsection
@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-6">
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="/" class="app-brand-link d-flex align-items-center">
                                <span class="app-brand-logo demo">
                                    <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                                        class="brand-image img-circle elevation-3 preview-app-logo" style="height: 50px;">
                                </span>
                                <span class="app-brand-text demo text-heading fw-bold">{{ config('setting.app_name') }}</span>
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-1">Welcome to {{ config('setting.app_name') }} 👋</h4>
                        <p class="mb-6">Please Log-in to your account</p>

                        <form id="login-form" class="mb-4" action="{{ url('/api/auth/login') }}" method="POST">
                            @csrf
                            <div class="mb-6">
                                <label for="login-email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="login-email" name="email"
                                    placeholder="Enter your email" autocomplete="username webauthn" autofocus />
                                <label id="email-error" class="error" for="login-email" style="display:none;"></label>
                            </div>
                            <div class="mb-6 form-password-toggle form-control-validation">
                                <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge has-validation">
                                    <input type="password" id="password" class="form-control" name="password"
                                        autocomplete="current-password" aria-describedby="password" />
                                    <span class="input-group-text cursor-pointer" data-password-toggle="#password">
                                        <i class="icon-base bx bx-hide"></i>
                                    </span>
                                </div>
                                <label id="password-error" class="error" for="password" style="display:none;"></label>
                            </div>
                            <div class="my-8">
                                <div class="d-flex justify-content-between">
                                    <div class="form-check mb-0 ms-2">
                                        <input class="form-check-input" type="checkbox" id="remember-me" name="remember"
                                            value="1" />
                                        <label class="form-check-label" for="remember-me"> Remember Me </label>
                                    </div>
                                    <a href="{{ url('/password-forgot') }}">
                                        <p class="mb-0">Forgot Password?</p>
                                    </a>
                                </div>
                            </div>

                            <div class="mb-6">
                                <button class="btn btn-primary d-grid w-100 mb-4" type="submit" id="login-submit">Login</button>
                            </div>
                        </form>

                        <button type="button" class="btn btn-outline-secondary d-grid w-100 mb-4" id="passkey-login-btn"
                            data-options-url="{{ url('/api/auth/passkey/login-options') }}"
                            data-verify-url="{{ url('/api/auth/passkey/login-verify') }}"
                            data-dashboard-url="{{ url('/dashboard') }}">
                            Sign in with a passkey
                        </button>

                        <button type="button" class="btn btn-outline-secondary d-grid w-100 mb-4" id="magic-link-toggle"
                            data-start-url="{{ url('/api/auth/login-link') }}"
                            data-poll-url="{{ url('/api/auth/login-link/poll') }}"
                            data-dashboard-url="{{ url('/dashboard') }}">
                            Login with Magic Link
                        </button>

                        @if (config('services.google.client_id'))
                            <a href="{{ url('/api/auth/google') }}" class="btn btn-outline-secondary d-grid w-100 mb-4">
                                Sign in with Google
                            </a>
                        @endif

                        <div id="magic-link-panel" style="display:none;" class="mb-4">
                            <div id="magic-link-start">
                                <div class="mb-4">
                                    <label for="magic-link-email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="magic-link-email" placeholder="Enter your email" />
                                </div>
                                <button type="button" class="btn btn-primary d-grid w-100" id="magic-link-send">Send Login Link</button>
                            </div>
                            <div id="magic-link-waiting" style="display:none;" class="text-center">
                                <div class="spinner-border text-primary mb-3" role="status"></div>
                                <p class="mb-2">We sent a login link to your email. Open it on any device to continue.</p>
                                <p class="mb-1">Confirm this code matches:</p>
                                <p class="mb-3">
                                    <span id="magic-link-code" style="font-family:monospace;font-size:1.5rem;letter-spacing:0.2em;background:#f5f5f5;border-radius:999px;padding:0.4rem 1.2rem;display:inline-block;"></span>
                                </p>
                                <p id="magic-link-status" class="text-danger mb-0" style="display:none;"></p>
                            </div>
                        </div>

                        <p class="text-center">
                            <span>New on our platform?</span>
                            <a href="{{ url('/register') }}">
                                <span>Create an account</span>
                            </a>
                        </p>
                    </div>
                </div>
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

            if (!passkeyAuth.isSupported()) {
                btn.style.display = 'none';
                return;
            }

            btn.addEventListener('click', function () {
                btn.disabled = true;
                passkeyAuth.login(btn.dataset.optionsUrl, btn.dataset.verifyUrl, function (response) {
                    btn.disabled = false;
                    if (response.status == 1) {
                        window.location.href = btn.dataset.dashboardUrl;
                    } else {
                        app.showMessage(response.message, 'error');
                    }
                });
            });
        })();
    </script>
    <script>
        document.getElementById('login-form').addEventListener('submit', function (event) {
            event.preventDefault();

            var email = document.getElementById('login-email');
            var password = document.getElementById('password');

            if (!email.value) {
                app.showMessage('Please enter your email.', 'error');
                return;
            }
            if (!password.value) {
                app.showMessage('Please enter your password.', 'error');
                return;
            }

            app.ajaxForm(this, function (response) {
                if (response.status == 1) {
                    var next = response.data && response.data.next;

                    if (next === 'tfa') {
                        window.location.href = '{{ url('/verify') }}?type=tfa';
                        return;
                    }

                    window.location.href = '{{ url('/dashboard') }}';
                } else {
                    var data = response.data || {};

                    if (data.next === 'verify-account') {
                        window.location.href = '{{ url('/verify-account') }}?code=' + btoa(data.email || email.value);
                        return;
                    }

                    app.showMessage(response.message, 'error');
                }
            });
        });
    </script>
@endpush

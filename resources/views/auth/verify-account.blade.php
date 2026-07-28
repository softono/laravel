@extends('layouts.blank')
@section('title')
    Verify Your Account
@endsection
@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-6">
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <div class="app-brand justify-content-center">
                            <a href="{{ url('/') }}" class="app-brand-link d-flex align-items-center">
                                <span class="app-brand-logo demo">
                                    <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                                        class="brand-image img-circle elevation-3 preview-app-logo" style="height: 50px;">
                                </span>
                                <span class="app-brand-text demo text-heading fw-bold">{{ config('setting.app_name') }}</span>
                            </a>
                        </div>
                        <h4 class="mb-1">Verify Your Account</h4>
                        <p class="mb-6">We've sent a 6-digit code to your email. Enter it below to verify your account.</p>

                        <form id="verify-form" class="mb-4" action="{{ url('/api/auth/verify-account') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ $email }}" placeholder="Enter your email" {{ $email ? 'readonly' : '' }} />
                            </div>
                            <div class="mb-6">
                                <label for="otp" class="form-label">OTP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="otp" name="otp" maxlength="6"
                                    inputmode="numeric" placeholder="Enter the 6-digit OTP" autofocus />
                            </div>
                            <button class="btn btn-primary d-grid w-100 mb-4" type="submit" id="verify-submit">Verify</button>
                        </form>

                        <p class="text-center">
                            <button type="button" class="btn btn-link p-0" id="resend-otp" data-resend-seconds="60">
                                Resend OTP
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/auth/countdown.js') }}"></script>
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
            app.ajaxPost('{{ url('/api/auth/otp') }}', {
                email: document.getElementById('email').value,
                purpose: 'verify'
            }, function (response) {
                app.showMessage(response.message, response.status ? 'success' : 'error');
            });
        });
    </script>
@endpush

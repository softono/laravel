@extends('admin.layouts.blank')
@section('title')
    Verify Your Account
@endsection
@section('content')
    <div class="w-full max-w-md">
        <div class="card">
            <div class="card-body p-6 sm:p-8">
                <div class="mb-6 flex justify-center">
                    <a href="{{ url('/admin/auth/login') }}" class="flex items-center gap-2">
                        <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                            class="h-10 w-10 rounded-full object-cover" alt="">
                        <span class="text-lg font-bold text-slate-800">{{ config('setting.app_name') }}</span>
                    </a>
                </div>
                <h4 class="mb-1 text-xl font-semibold text-slate-800">Verify Your Account</h4>
                <p class="mb-6 text-sm text-slate-500">We've sent a 6-digit code to your email. Enter it below to verify your account.</p>

                <form id="verify-form" class="mb-4" action="{{ url('/auth/verify-account') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="email" class="form-label">Email <span class="text-rose-600">*</span></label>
                        <input type="email" class="form-input" id="email" name="email"
                            value="{{ $email }}" placeholder="Enter your email" {{ $email ? 'readonly' : '' }} />
                    </div>
                    <div class="mb-6">
                        <label for="otp" class="form-label">OTP <span class="text-rose-600">*</span></label>
                        <input type="text" class="form-input" id="otp" name="otp" maxlength="6"
                            inputmode="numeric" placeholder="Enter the 6-digit OTP" autofocus />
                    </div>
                    <button class="btn-primary w-full mb-4" type="submit" id="verify-submit">Verify</button>
                </form>

                <p class="text-center">
                    <button type="button" class="text-sm text-primary-600 hover:underline" id="resend-otp" data-resend-seconds="60">
                        Resend OTP
                    </button>
                </p>
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
                    window.location.href = '{{ url('/admin/auth/login') }}';
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

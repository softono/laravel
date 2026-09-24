@extends('layouts.blank')
@section('title')
    Forgot Password
@endsection
@section('content')
    <div class="w-full max-w-md">
        <div class="card">
            <div class="card-body p-6 sm:p-8">
                <div class="mb-6 flex justify-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                            class="h-10 w-10 rounded-full object-cover" alt="">
                        <span class="text-lg font-bold text-slate-800">{{ config('setting.app_name') }}</span>
                    </a>
                </div>
                <h4 class="mb-1 text-xl font-semibold text-slate-800">Forgot Password?</h4>
                <p class="mb-6 text-sm text-slate-500">Enter your email and we'll send you an OTP to reset your password.</p>

                <form id="forgot-form" class="mb-4" action="{{ url('/auth/forgot-password') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="email" class="form-label">Email <span class="text-rose-600">*</span></label>
                        <input type="email" class="form-input" id="email" name="email"
                            placeholder="Enter your email" autofocus />
                    </div>
                    <button class="btn-primary w-full mb-4" type="submit" id="forgot-submit">Send OTP</button>
                </form>

                <p class="text-center">
                    <a href="{{ url('/login') }}" class="inline-flex items-center gap-1 text-sm text-primary-600 hover:underline">
                        <i class="bx bx-chevron-left"></i> Back to login
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        document.getElementById('forgot-form').addEventListener('submit', function (event) {
            event.preventDefault();

            var email = document.getElementById('email').value;
            if (!email) {
                app.showMessage('Please enter your email.', 'error');
                return;
            }

            var btn = document.getElementById('forgot-submit');
            btn.disabled = true;

            app.ajaxForm(this, function (response) {
                btn.disabled = false;
                app.showMessage(response.message, response.status ? 'success' : 'error');

                if (response.status == 1) {
                    window.location.href = '{{ url('/reset-password') }}?email=' + encodeURIComponent(email);
                }
            });
        });
    </script>
@endpush

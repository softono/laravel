@extends('modules.admin.layouts.blank')
@section('title')
    Reset Password
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
                <h4 class="mb-1 text-xl font-semibold text-slate-800">Reset Password</h4>
                <p class="mb-6 text-sm text-slate-500">Enter the OTP we sent you and choose a new password.</p>

                <form id="reset-form" class="mb-4" action="{{ url('/auth/reset-password') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="email" class="form-label">Email <span class="text-rose-600">*</span></label>
                        <input type="email" class="form-input" id="email" name="email"
                            value="{{ request('email') }}" placeholder="Enter your email" />
                    </div>
                    <div class="mb-4">
                        <label for="otp" class="form-label">OTP <span class="text-rose-600">*</span></label>
                        <input type="text" class="form-input" id="otp" name="otp" maxlength="6"
                            inputmode="numeric" placeholder="Enter the 6-digit OTP" autofocus />
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="password">New Password <span class="text-rose-600">*</span></label>
                        <div class="input-group">
                            <input type="password" id="password" class="form-input" name="password"
                                placeholder="Enter a new password" aria-describedby="password" />
                            <span class="input-group-text cursor-pointer" data-password-toggle="#password">
                                <i class="bx bx-hide"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mb-6">
                        <label class="form-label" for="confirm_password">Confirm Password <span class="text-rose-600">*</span></label>
                        <div class="input-group">
                            <input type="password" id="confirm_password" class="form-input"
                                name="confirm_password" placeholder="Re-enter the new password"
                                aria-describedby="confirm_password" />
                            <span class="input-group-text cursor-pointer" data-password-toggle="#confirm_password">
                                <i class="bx bx-hide"></i>
                            </span>
                        </div>
                    </div>
                    <button class="btn-primary w-full mb-4" type="submit" id="reset-submit">Reset Password</button>
                </form>

                <p class="text-center">
                    <a href="{{ url('/admin/auth/login') }}" class="inline-flex items-center gap-1 text-sm text-primary-600 hover:underline">
                        <i class="bx bx-chevron-left"></i> Back to login
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/auth/password-toggle.js') }}"></script>
    <script>
        document.getElementById('reset-form').addEventListener('submit', function (event) {
            event.preventDefault();

            if (document.getElementById('password').value !== document.getElementById('confirm_password').value) {
                app.showMessage('Passwords do not match.', 'error');
                return;
            }

            var btn = document.getElementById('reset-submit');
            btn.disabled = true;

            app.ajaxForm(this, function (response) {
                btn.disabled = false;
                app.showMessage(response.message, response.status ? 'success' : 'error');

                if (response.status == 1) {
                    window.location.href = '{{ url('/admin/auth/login') }}';
                }
            });
        });
    </script>
@endpush

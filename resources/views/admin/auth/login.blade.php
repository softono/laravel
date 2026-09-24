@extends('admin.layouts.blank')
@section('title')
    Login To Your Account
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
                <h4 class="mb-1 text-xl font-semibold text-slate-800">Welcome to {{ config('setting.app_name') }} 👋</h4>
                <p class="mb-6 text-sm text-slate-500">Please Log-in to your admin account</p>

                <form id="login-form" class="mb-4" action="{{ url('/admin/auth/login') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="login-email" class="form-label">Email <span class="text-rose-600">*</span></label>
                        <input type="email" class="form-input" id="login-email" name="email"
                            placeholder="Enter your email" autocomplete="username" autofocus />
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
                    </div>
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <input class="form-check-input" type="checkbox" id="remember-me" name="remember"
                                    value="1" />
                                <label class="form-check-label" for="remember-me"> Remember Me </label>
                            </div>
                            <a href="{{ url('/admin/auth/password-forgot') }}" class="text-sm text-primary-600 hover:underline">
                                Forgot Password?
                            </a>
                        </div>
                    </div>

                    <div class="mb-6">
                        <button class="btn-primary w-full" type="submit" id="login-submit">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/auth/password-toggle.js') }}"></script>
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
                        window.location.href = '{{ url('/admin/auth/verify') }}?type=tfa';
                        return;
                    }

                    window.location.href = '{{ url('/admin/dashboard') }}';
                } else {
                    app.showMessage(response.message, 'error');
                }
            });
        });
    </script>
@endpush

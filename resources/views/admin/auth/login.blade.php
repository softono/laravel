@extends('admin.layouts.blank')
@section('title')
    Login To Your Account
@endsection
@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-6">
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <div class="app-brand justify-content-center">
                            <a href="{{ url('/admin/auth/login') }}" class="app-brand-link d-flex align-items-center">
                                <span class="app-brand-logo demo">
                                    <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                                        class="brand-image img-circle elevation-3 preview-app-logo" style="height: 50px;">
                                </span>
                                <span class="app-brand-text demo text-heading fw-bold">{{ config('setting.app_name') }}</span>
                            </a>
                        </div>
                        <h4 class="mb-1">Welcome to {{ config('setting.app_name') }} 👋</h4>
                        <p class="mb-6">Please Log-in to your admin account</p>

                        <form id="login-form" class="mb-4" action="{{ url('/api/admin/auth/login') }}" method="POST">
                            @csrf
                            <div class="mb-6">
                                <label for="login-email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="login-email" name="email"
                                    placeholder="Enter your email" autocomplete="username" autofocus />
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
                            </div>
                            <div class="my-8">
                                <div class="d-flex justify-content-between">
                                    <div class="form-check mb-0 ms-2">
                                        <input class="form-check-input" type="checkbox" id="remember-me" name="remember"
                                            value="1" />
                                        <label class="form-check-label" for="remember-me"> Remember Me </label>
                                    </div>
                                    <a href="{{ url('/admin/auth/password-forgot') }}">
                                        <p class="mb-0">Forgot Password?</p>
                                    </a>
                                </div>
                            </div>

                            <div class="mb-6">
                                <button class="btn btn-primary d-grid w-100 mb-4" type="submit" id="login-submit">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
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

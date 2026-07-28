@extends('layouts.blank')
@section('title')
    Reset Password
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
                        <h4 class="mb-1">Reset Password</h4>
                        <p class="mb-6">Enter the OTP we sent you and choose a new password.</p>

                        <form id="reset-form" class="mb-4" action="{{ url('/api/auth/reset-password') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ request('email') }}" placeholder="Enter your email" />
                            </div>
                            <div class="mb-4">
                                <label for="otp" class="form-label">OTP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="otp" name="otp" maxlength="6"
                                    inputmode="numeric" placeholder="Enter the 6-digit OTP" autofocus />
                            </div>
                            <div class="mb-4 form-password-toggle form-control-validation">
                                <label class="form-label" for="password">New Password <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge has-validation">
                                    <input type="password" id="password" class="form-control" name="password"
                                        placeholder="Enter a new password" aria-describedby="password" />
                                    <span class="input-group-text cursor-pointer" data-password-toggle="#password">
                                        <i class="icon-base bx bx-hide"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-6 form-password-toggle form-control-validation">
                                <label class="form-label" for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge has-validation">
                                    <input type="password" id="confirm_password" class="form-control"
                                        name="confirm_password" placeholder="Re-enter the new password"
                                        aria-describedby="confirm_password" />
                                    <span class="input-group-text cursor-pointer" data-password-toggle="#confirm_password">
                                        <i class="icon-base bx bx-hide"></i>
                                    </span>
                                </div>
                            </div>
                            <button class="btn btn-primary d-grid w-100 mb-4" type="submit" id="reset-submit">Reset Password</button>
                        </form>

                        <p class="text-center">
                            <a href="{{ url('/login') }}">
                                <i class="icon-base bx bx-chevron-left"></i> Back to login
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
                    window.location.href = '{{ url('/login') }}';
                }
            });
        });
    </script>
@endpush

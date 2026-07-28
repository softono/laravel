@extends('layouts.blank')
@section('title')
    Register Your Account
@endsection
@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-4">
                <div class="card">
                    <div class="card-body">
                        <div class="app-brand justify-content-center mb-4 mt-2">
                            <a href="{{ url('/') }}" class="app-brand-link d-flex align-items-center">
                                <span class="app-brand-logo demo">
                                    <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                                        class="brand-image img-circle elevation-3 preview-app-logo" style="height: 50px;">
                                </span>
                                <span class="app-brand-text demo text-body fw-bold ms-1">{{ config('setting.app_name') }}</span>
                            </a>
                        </div>
                        <h4 class="mb-1 pt-2" style="text-align: center;">Welcome to {{ config('setting.app_name') }}</h4>
                        <p class="mb-4" style="text-align: center;">Create your account</p>

                        <form class="mb-3" action="{{ url('/api/auth/register') }}" method="POST" id="register-form">
                            @csrf
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required
                                    placeholder="Enter your first name" maxlength="50" autofocus />
                            </div>
                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required
                                    placeholder="Enter your last name" maxlength="50" />
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" required
                                    placeholder="Enter your 10-digit phone number" maxlength="10" />
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required
                                    placeholder="Enter your email address" />
                            </div>
                            <div class="mb-3 form-password-toggle form-control-validation">
                                <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge has-validation">
                                    <input type="password" id="password" class="form-control" required name="password"
                                        placeholder="Create a password" aria-describedby="password" />
                                    <span class="input-group-text cursor-pointer" data-password-toggle="#password">
                                        <i class="icon-base bx bx-hide"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3 form-password-toggle form-control-validation">
                                <label class="form-label" for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge has-validation">
                                    <input type="password" id="confirm_password" class="form-control" required
                                        name="confirm_password" placeholder="Re-enter your password"
                                        aria-describedby="confirm_password" />
                                    <span class="input-group-text cursor-pointer" data-password-toggle="#confirm_password">
                                        <i class="icon-base bx bx-hide"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agree" name="agree" value="1" />
                                    <label class="form-check-label" for="agree">
                                        I agree to
                                        <a target="_blank" href="{{ url('/page/privacy-policy') }}">privacy policy &amp; terms</a>
                                    </label>
                                </div>
                            </div>

                            <button class="btn btn-primary d-grid w-100" type="submit">Sign up</button>
                        </form>

                        @if (config('services.google.client_id'))
                            <a href="{{ url('/api/auth/google') }}" class="btn btn-outline-secondary d-grid w-100 mb-3">
                                Register with Google
                            </a>
                        @endif

                        <p class="text-center">
                            <span>Already have an account?</span>
                            <a href="{{ url('/login') }}">
                                <span>Log in instead</span>
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
        document.getElementById('register-form').addEventListener('submit', function (event) {
            event.preventDefault();

            if (document.getElementById('password').value !== document.getElementById('confirm_password').value) {
                app.showMessage('Passwords do not match.', 'error');
                return;
            }
            if (!document.getElementById('agree').checked) {
                app.showMessage('You must agree to the privacy policy and terms.', 'error');
                return;
            }

            app.ajaxForm(this, function (response) {
                if (response.status == 1) {
                    var data = response.data || {};

                    if (data.next === 'verify-account') {
                        window.location.href = '{{ url('/verify-account') }}?code=' + btoa(data.email);
                        return;
                    }

                    window.location.href = '{{ url('/dashboard') }}';
                } else {
                    app.showMessage(response.message, 'error');
                }
            });
        });
    </script>
@endpush

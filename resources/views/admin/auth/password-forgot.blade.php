@extends('admin.layouts.blank')
@section('title')
    Forgot Password
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
                        <h4 class="mb-1">Forgot Password?</h4>
                        <p class="mb-6">Enter your email and we'll send you an OTP to reset your password.</p>

                        <form id="forgot-form" class="mb-4" action="{{ url('/api/auth/forgot-password') }}" method="POST">
                            @csrf
                            <div class="mb-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                    placeholder="Enter your email" autofocus />
                            </div>
                            <button class="btn btn-primary d-grid w-100 mb-4" type="submit" id="forgot-submit">Send OTP</button>
                        </form>

                        <p class="text-center">
                            <a href="{{ url('/admin/auth/login') }}">
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
                    window.location.href = '{{ url('/admin/auth/reset-password') }}?email=' + encodeURIComponent(email);
                }
            });
        });
    </script>
@endpush

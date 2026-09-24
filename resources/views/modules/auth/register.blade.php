@extends('layouts.blank')
@section('title')
    Register Your Account
@endsection
@section('content')
    <div class="w-full max-w-md">
        <div class="card">
            <div class="card-body p-6 sm:p-8">
                <div class="mb-4 mt-2 flex justify-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                            class="h-10 w-10 rounded-full object-cover" alt="">
                        <span class="text-lg font-bold text-slate-800">{{ config('setting.app_name') }}</span>
                    </a>
                </div>
                <h4 class="mb-1 pt-2 text-center text-xl font-semibold text-slate-800">Welcome to {{ config('setting.app_name') }}</h4>
                <p class="mb-4 text-center text-sm text-slate-500">Create your account</p>

                <form class="mb-3" action="{{ url('/auth/register') }}" method="POST" id="register-form">
                    @csrf
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name <span class="text-rose-600">*</span></label>
                        <input type="text" class="form-input" id="first_name" name="first_name" required
                            placeholder="Enter your first name" maxlength="50" autofocus />
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name <span class="text-rose-600">*</span></label>
                        <input type="text" class="form-input" id="last_name" name="last_name" required
                            placeholder="Enter your last name" maxlength="50" />
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number <span class="text-rose-600">*</span></label>
                        <input type="tel" class="form-input" id="phone" name="phone" required
                            placeholder="Enter your 10-digit phone number" maxlength="10" />
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-rose-600">*</span></label>
                        <input type="email" class="form-input" id="email" name="email" required
                            placeholder="Enter your email address" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Password <span class="text-rose-600">*</span></label>
                        <div class="input-group">
                            <input type="password" id="password" class="form-input" required name="password"
                                placeholder="Create a password" aria-describedby="password" />
                            <span class="input-group-text cursor-pointer" data-password-toggle="#password">
                                <i class="bx bx-hide"></i>
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="confirm_password">Confirm Password <span class="text-rose-600">*</span></label>
                        <div class="input-group">
                            <input type="password" id="confirm_password" class="form-input" required
                                name="confirm_password" placeholder="Re-enter your password"
                                aria-describedby="confirm_password" />
                            <span class="input-group-text cursor-pointer" data-password-toggle="#confirm_password">
                                <i class="bx bx-hide"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="flex items-center gap-2">
                            <input class="form-check-input" type="checkbox" id="agree" name="agree" value="1" />
                            <label class="form-check-label" for="agree">
                                I agree to
                                <a target="_blank" href="{{ url('/page/privacy-policy') }}" class="text-primary-600 hover:underline">privacy policy &amp; terms</a>
                            </label>
                        </div>
                    </div>

                    @include('common.recaptcha')
                    <button class="btn-primary w-full" type="submit">Sign up</button>
                </form>

                @if (config('services.google.client_id'))
                    <a href="{{ url('/auth/google') }}" class="btn-outline w-full mb-3 inline-flex items-center justify-center">
                        Register with Google
                    </a>
                @endif

                <p class="text-center text-sm text-slate-500">
                    <span>Already have an account?</span>
                    <a href="{{ url('/login') }}" class="text-primary-600 hover:underline">
                        <span>Log in instead</span>
                    </a>
                </p>
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

                    if (data.requires_verification) {
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

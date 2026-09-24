@extends('layouts.blank')
@section('title')
    Register Your Account
@endsection
@section('content')
    <div class="w-full max-w-md">
        <x-ui.card>
            <x-ui.card-content>
                <div class="mb-4 mt-2 flex justify-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                            class="h-10 w-10 rounded-full object-cover" alt="">
                        <span class="text-lg font-bold text-foreground">{{ config('setting.app_name') }}</span>
                    </a>
                </div>
                <h4 class="mb-1 pt-2 text-center text-xl font-semibold text-foreground">Welcome to {{ config('setting.app_name') }}</h4>
                <p class="mb-4 text-center text-sm text-muted-foreground">Create your account</p>

                <form class="mb-3" action="{{ url('/auth/register') }}" method="POST" id="register-form">
                    @csrf
                    <div class="mb-3">
                        <x-ui.label for="first_name" class="mb-2">First Name <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input type="text" id="first_name" name="first_name" required
                            placeholder="Enter your first name" maxlength="50" autofocus />
                    </div>
                    <div class="mb-3">
                        <x-ui.label for="last_name" class="mb-2">Last Name <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input type="text" id="last_name" name="last_name" required
                            placeholder="Enter your last name" maxlength="50" />
                    </div>
                    <div class="mb-3">
                        <x-ui.label for="phone" class="mb-2">Phone Number <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input type="tel" id="phone" name="phone" required
                            placeholder="Enter your 10-digit phone number" maxlength="10" />
                    </div>
                    <div class="mb-3">
                        <x-ui.label for="email" class="mb-2">Email <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input type="email" id="email" name="email" required
                            placeholder="Enter your email address" />
                    </div>
                    <div class="mb-3">
                        <x-ui.label class="mb-2" for="password">Password <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.password-input id="password" required name="password"
                                placeholder="Create a password" aria-describedby="password" />
                    </div>
                    <div class="mb-3">
                        <x-ui.label class="mb-2" for="confirm_password">Confirm Password <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.password-input id="confirm_password" required
                                name="confirm_password" placeholder="Re-enter your password"
                                aria-describedby="confirm_password" />
                    </div>

                    <div class="mb-3">
                        <div class="flex items-center gap-2">
                            <x-ui.checkbox id="agree" name="agree" value="1" />
                            <x-ui.label class="font-normal" for="agree">
                                I agree to
                                <a target="_blank" href="{{ url('/page/privacy-policy') }}" class="text-primary hover:underline">privacy policy &amp; terms</a>
                            </x-ui.label>
                        </div>
                    </div>

                    @include('common.recaptcha')
                    <x-ui.button class="w-full" type="submit">Sign up</x-ui.button>
                </form>

                @if (config('services.google.client_id'))
                    <x-ui.button variant="outline" href="{{ url('/auth/google') }}" class="w-full mb-3 inline-flex items-center justify-center">
                        Register with Google
                    </x-ui.button>
                @endif

                <p class="text-center text-sm text-muted-foreground">
                    <span>Already have an account?</span>
                    <a href="{{ url('/login') }}" class="text-primary hover:underline">
                        <span>Log in instead</span>
                    </a>
                </p>
            </x-ui.card-content>
        </x-ui.card>
    </div>
@endsection
@push('scripts')
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

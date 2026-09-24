@extends('layouts.blank')
@section('title')
    Reset Password
@endsection
@section('content')
    <div class="w-full max-w-md">
        <x-ui.card>
            <x-ui.card-content>
                <div class="mb-6 flex justify-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                            class="h-10 w-10 rounded-full object-cover" alt="">
                        <span class="text-lg font-bold text-foreground">{{ config('setting.app_name') }}</span>
                    </a>
                </div>
                <h4 class="mb-1 text-xl font-semibold text-foreground">Reset Password</h4>
                <p class="mb-6 text-sm text-muted-foreground">Enter the OTP we sent you and choose a new password.</p>

                <form id="reset-form" class="mb-4" action="{{ url('/auth/reset-password') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <x-ui.label for="email" class="mb-2">Email <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input type="email" id="email" name="email"
                            value="{{ request('email') }}" placeholder="Enter your email" />
                    </div>
                    <div class="mb-4">
                        <x-ui.label for="otp" class="mb-2">OTP <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input type="text" id="otp" name="otp" maxlength="6"
                            inputmode="numeric" placeholder="Enter the 6-digit OTP" autofocus />
                    </div>
                    <div class="mb-4">
                        <x-ui.label class="mb-2" for="password">New Password <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.password-input id="password" name="password"
                                placeholder="Enter a new password" aria-describedby="password" />
                    </div>
                    <div class="mb-6">
                        <x-ui.label class="mb-2" for="confirm_password">Confirm Password <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.password-input id="confirm_password"
                                name="confirm_password" placeholder="Re-enter the new password"
                                aria-describedby="confirm_password" />
                    </div>
                    <x-ui.button class="w-full mb-4" type="submit" id="reset-submit">Reset Password</x-ui.button>
                </form>

                <p class="text-center">
                    <a href="{{ url('/login') }}" class="inline-flex items-center gap-1 text-sm text-primary hover:underline">
                        <i class="bx bx-chevron-left"></i> Back to login
                    </a>
                </p>
            </x-ui.card-content>
        </x-ui.card>
    </div>
@endsection
@push('scripts')
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

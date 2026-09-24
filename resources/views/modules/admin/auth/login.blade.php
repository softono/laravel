@extends('modules.admin.layouts.blank')
@section('title')
    Login To Your Account
@endsection
@section('content')
    <div class="w-full max-w-md">
        <x-ui.card>
            <x-ui.card-content>
                <div class="mb-6 flex justify-center">
                    <a href="{{ url('/admin/auth/login') }}" class="flex items-center gap-2">
                        <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                            class="h-10 w-10 rounded-full object-cover" alt="">
                        <span class="text-lg font-bold text-foreground">{{ config('setting.app_name') }}</span>
                    </a>
                </div>
                <h4 class="mb-1 text-xl font-semibold text-foreground">Welcome to {{ config('setting.app_name') }} 👋</h4>
                <p class="mb-6 text-sm text-muted-foreground">Please Log-in to your admin account</p>

                <form id="login-form" class="mb-4" action="{{ url('/admin/auth/login') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <x-ui.label for="login-email" class="mb-2">Email <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input type="email" id="login-email" name="email"
                            placeholder="Enter your email" autocomplete="username" autofocus />
                    </div>
                    <div class="mb-6">
                        <x-ui.label class="mb-2" for="password">Password <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.password-input id="password" name="password"
                                autocomplete="current-password" aria-describedby="password" />
                    </div>
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-ui.checkbox id="remember-me" name="remember"
                                    value="1" />
                                <x-ui.label class="font-normal" for="remember-me"> Remember Me </x-ui.label>
                            </div>
                            <a href="{{ url('/admin/auth/password-forgot') }}" class="text-sm text-primary hover:underline">
                                Forgot Password?
                            </a>
                        </div>
                    </div>

                    {{-- Shown by the script once the server asks for it after repeated failures. --}}
                    <div id="login-captcha" style="display:none;">
                        @include('common.recaptcha')
                    </div>

                    <div class="mb-6">
                        <x-ui.button class="w-full" type="submit" id="login-submit">Login</x-ui.button>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>
    </div>
@endsection
@push('scripts')
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
                    if (response.data && response.data.requires_tfa) {
                        window.location.href = '{{ url('/admin/auth/verify') }}?type=tfa&redirect=' + encodeURIComponent(@json($redirectPath));
                        return;
                    }

                    window.location.href = @json(url($redirectPath));
                } else {
                    if (response.data && response.data.requires_captcha) {
                        document.getElementById('login-captcha').style.display = 'block';
                    }
                    app.showMessage(response.message, 'error');
                }
            });
        });
    </script>
@endpush

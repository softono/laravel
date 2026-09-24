@extends('modules.admin.layouts.blank')
@section('title')
    Forgot Password
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
                <h4 class="mb-1 text-xl font-semibold text-foreground">Forgot Password?</h4>
                <p class="mb-6 text-sm text-muted-foreground">Enter your email and we'll send you an OTP to reset your password.</p>

                <form id="forgot-form" class="mb-4" action="{{ url('/auth/forgot-password') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <x-ui.label for="email" class="mb-2">Email <span class="text-destructive">*</span></x-ui.label>
                        <x-ui.input type="email" id="email" name="email"
                            placeholder="Enter your email" autofocus />
                    </div>
                    @include('common.recaptcha')
                    <x-ui.button class="w-full mb-4" type="submit" id="forgot-submit">Send OTP</x-ui.button>
                </form>

                <p class="text-center">
                    <a href="{{ url('/admin/auth/login') }}" class="inline-flex items-center gap-1 text-sm text-primary hover:underline">
                        <i class="bx bx-chevron-left"></i> Back to login
                    </a>
                </p>
            </x-ui.card-content>
        </x-ui.card>
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

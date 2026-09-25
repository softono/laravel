@extends('modules.admin.layouts.blank')
@section('title')
    Two-Factor Verification
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
                <h4 class="mb-1 text-xl font-semibold text-foreground">Two-Factor Verification</h4>
                <p class="mb-4 text-sm text-muted-foreground">Choose how you'd like to verify it's you.</p>

                <div id="tfa-method-list" class="mb-4"></div>

                <form id="tfa-verify-form" style="display:none;" class="mb-4">
                    <input type="hidden" id="tfa-method" name="method" value="">
                    <div class="mb-4">
                        <x-ui.label for="tfa-code" class="mb-2" id="tfa-code-label">Code</x-ui.label>
                        <x-ui.input type="text" id="tfa-code" name="code" maxlength="8"
                            autofocus placeholder="Enter the code" />
                    </div>
                    <div class="mb-4">
                        <div class="flex items-center gap-2">
                            <x-ui.checkbox id="trust_device" name="trust_device" value="1" checked />
                            <x-ui.label class="font-normal" for="trust_device">Trust this device for 30 days</x-ui.label>
                        </div>
                    </div>
                    <x-ui.button class="w-full mb-2" type="submit" id="tfa-submit">Verify</x-ui.button>
                    <x-ui.button variant="outline" class="w-full inline-flex items-center justify-center gap-1" type="button" id="tfa-back">
                        <i class="bx bx-chevron-left"></i> Choose another method
                    </x-ui.button>
                </form>

                <div id="tfa-link-panel" style="display:none;" class="mb-4 text-center">
                    <div class="mb-3 flex justify-center">
                        <span class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-muted border-t-primary"></span>
                    </div>
                    <p class="mb-2">We sent a login link to your email. Open it on any device to continue.</p>
                    <p class="mb-1">Confirm this code matches:</p>
                    <p class="mb-3"><span id="tfa-link-code" class="inline-block rounded-full bg-muted px-5 py-1 font-mono text-2xl tracking-widest"></span></p>
                    <p id="tfa-link-status" class="mb-3 text-destructive" style="display:none;"></p>
                    <x-ui.button variant="outline" class="w-full" type="button" onclick="$('#tfa-back').click()">Choose another method</x-ui.button>
                </div>

                <p class="mb-0 text-center text-sm">
                    <a href="{{ url('/admin/auth/logout') }}" class="text-primary hover:underline">Cancel and log out</a>
                </p>
            </x-ui.card-content>
        </x-ui.card>
    </div>
@endsection
@push('scripts')
    <script src="{{ $general->assetUrl('assets/js/auth/tfa-verify.js') }}"></script>
    <script>
        tfaVerify.init({
            methodsUrl: '{{ url('/auth/tfa/methods') }}',
            sendOtpUrl: '{{ url('/auth/tfa/send-otp') }}',
            verifyUrl: '{{ url('/auth/tfa/verify') }}',
            sendLinkUrl: '{{ url('/auth/tfa/send-login-link') }}',
            pollUrl: '{{ url('/auth/login-link/poll') }}',
            dashboardUrl: @json(url($redirectPath)),
        });
    </script>
@endpush

@extends('layouts.blank')
@section('title')
    Two-Factor Verification
@endsection
@section('content')
    <div class="w-full max-w-md">
        <div class="card">
            <div class="card-body p-6 sm:p-8">
                <div class="mb-6 flex justify-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                            class="h-10 w-10 rounded-full object-cover" alt="">
                        <span class="text-lg font-bold text-slate-800">{{ config('setting.app_name') }}</span>
                    </a>
                </div>
                <h4 class="mb-1 text-xl font-semibold text-slate-800">Two-Factor Verification</h4>
                <p class="mb-4 text-sm text-slate-500">Choose how you'd like to verify it's you.</p>

                <div id="tfa-method-list" class="mb-4"></div>

                <form id="tfa-verify-form" style="display:none;" class="mb-4">
                    <input type="hidden" id="tfa-method" name="method" value="">
                    <div class="mb-4">
                        <label for="tfa-code" class="form-label" id="tfa-code-label">Code</label>
                        <input type="text" class="form-input" id="tfa-code" name="code" maxlength="8"
                            autofocus placeholder="Enter the code" />
                    </div>
                    <div class="mb-4">
                        <div class="flex items-center gap-2">
                            <input class="form-check-input" type="checkbox" id="trust_device" name="trust_device" value="1" checked />
                            <label class="form-check-label" for="trust_device">Trust this device for 30 days</label>
                        </div>
                    </div>
                    <button class="btn-primary w-full mb-2" type="submit" id="tfa-submit">Verify</button>
                    <button class="btn-outline w-full inline-flex items-center justify-center gap-1" type="button" id="tfa-back">
                        <i class="bx bx-chevron-left"></i> Choose another method
                    </button>
                </form>

                <div id="tfa-link-panel" style="display:none;" class="mb-4 text-center">
                    <div class="mb-3 flex justify-center">
                        <span class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600"></span>
                    </div>
                    <p class="mb-2">We sent a login link to your email. Open it on any device to continue.</p>
                    <p class="mb-1">Confirm this code matches:</p>
                    <p class="mb-3"><span id="tfa-link-code" class="inline-block rounded-full bg-slate-100 px-5 py-1 font-mono text-2xl tracking-widest"></span></p>
                    <p id="tfa-link-status" class="mb-3 text-rose-600" style="display:none;"></p>
                    <button class="btn-outline w-full" type="button" onclick="$('#tfa-back').click()">Choose another method</button>
                </div>

                <p class="mb-0 text-center text-sm">
                    <a href="{{ url('/logout') }}" class="text-primary-600 hover:underline">Cancel and log out</a>
                </p>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/auth/tfa-verify.js') }}"></script>
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

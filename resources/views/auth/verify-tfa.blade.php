@extends('layouts.blank')
@section('title')
    Two-Factor Verification
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
                        <h4 class="mb-1">Two-Factor Verification</h4>
                        <p class="mb-4">Choose how you'd like to verify it's you.</p>

                        <div id="tfa-method-list" class="mb-4"></div>

                        <form id="tfa-verify-form" style="display:none;" class="mb-4">
                            <input type="hidden" id="tfa-method" name="method" value="">
                            <div class="mb-4">
                                <label for="tfa-code" class="form-label" id="tfa-code-label">Code</label>
                                <input type="text" class="form-control" id="tfa-code" name="code" maxlength="8"
                                    autofocus placeholder="Enter the code" />
                            </div>
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="trust_device" name="trust_device" value="1" checked />
                                    <label class="form-check-label" for="trust_device">Trust this device for 30 days</label>
                                </div>
                            </div>
                            <button class="btn btn-primary d-grid w-100 mb-2" type="submit" id="tfa-submit">Verify</button>
                            <button class="btn btn-outline-secondary d-grid w-100" type="button" id="tfa-back">
                                <i class="icon-base bx bx-chevron-left"></i> Choose another method
                            </button>
                        </form>

                        <p class="text-center mb-0">
                            <a href="{{ url('/logout') }}">Cancel and log out</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/auth/tfa-verify.js') }}"></script>
    <script>
        tfaVerify.init({
            methodsUrl: '{{ url('/api/auth/tfa/methods') }}',
            sendOtpUrl: '{{ url('/api/auth/tfa/send-otp') }}',
            verifyUrl: '{{ url('/api/auth/tfa/verify') }}',
            dashboardUrl: '{{ url('/dashboard') }}',
        });
    </script>
@endpush

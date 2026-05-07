@extends('layouts.blank')
@section('title')
    Login with Otp
@endsection
@section('content')
    <div class="authentication-wrapper authentication-basic px-4">
        <div class="authentication-inner py-4">
            <!--  Two Steps Verification -->
            <div class="card">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-4 mt-2">
                        <a href="index.html" class="app-brand-link gap-2 pjax">
                            <span class="app-brand-logo demo">
                                <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                                    class="brand-image img-circle elevation-3 preview-app-logo" style="height: 200%;">
                            </span>
                            <!--<span class="app-brand-text demo text-body fw-bold ms-1">{{ Config::get('setting.app_name') }}</span>-->
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h4 class="mb-1 pt-2">
                        Two Step Verification
                    </h4>
                    <p class="text-start mb-4">
                        OTP is sent on your Email Address.
                    </p>
                    {{ view('common/message_alert') }}
                    <p class="mb-0 fw-semibold">Type your 6 digit OTP</p>
                    <form id="ajax-form" action="{{ route('auth/verify-process') }}" method="post">
                        <input type="hidden" name="type" value="{{ $type }}">
                        {{ csrf_field() }}
                        <div class="mb-3">
                            <label class="form-label">OTP</label>
                            <input name="otp" type="number" class="form-control" required maxlength="6" minlength="6"
                                autofocus />
                        </div>
                        @if ($type == 'tfa')
                            <div class="mb-3">
                                <div class="form-check" style="display: flex;justify-content: space-between;">
                                    <input class="form-check-input" type="checkbox" id="skip_tfa" name="skip_tfa"
                                        value="1" checked />
                                    <label class="form-check-label" for="skip_tfa" style="padding-right: 95px;"> Ignore this
                                        device next time </label>
                                </div>
                            </div>
                        @endif
                        <button class="btn btn-primary d-grid w-100 mb-3">Submit</button>
                        <div class="form-group mb-8">
                            <a href="{{ route('logout') }}" class="btn btn-default d-grid w-100 noroute ">Logout</a>
                        </div>
                        <div class="text-center">
                            Didn't get the code?
                            <a href="javascript:void(0)" onclick="resendOtp()" id="resend-otp-link">Resend</a>
                        </div>
                    </form>
                </div>
            </div>
            <!-- / Two Steps Verification -->
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            // Ensure device cookie set before form submit (like login)
            let cookieName = "{{ config('setting.app_uid') }}_token";
            let deviceToken = document.cookie.match(new RegExp(cookieName + "=([^;]+)"))?.[1];
            if (!deviceToken) {
                deviceToken = btoa(String.fromCharCode(...crypto.getRandomValues(new Uint8Array(32))));
                document.cookie = cookieName + "=" + deviceToken + "; path=/; max-age=31536000; SameSite=Lax";
                console.log('Generated device cookie:', deviceToken);
            }

            $('#ajax-form').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            });
        });

        function resendOtp() {
            app.ajaxPost("{{ route('auth/resend-otp') }}", {
                type: '{{ $type }}'
            })
        }
    </script>
@endpush

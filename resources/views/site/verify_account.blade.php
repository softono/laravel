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
                    <a href="index.html" class="app-brand-link gap-2">
                        <span class="app-brand-logo demo">
                            <img src="{{$general->getFileUrl(config('setting.app_logo'),'logo')}}" class="brand-image img-circle elevation-3 preview-app-logo" style="height: 200%;">
                        </span>
                    </a>
                </div>
                <!-- /Logo -->
                <h4 class="mb-1 pt-2">
                    Verify Your Account
                </h4>
                <p class="text-start mb-4">
                    OTP is sent on your Email Address.
                </p>
                {{ view('common/message_alert') }}
                <p class="mb-0 fw-semibold">Type your 6 digit OTP</p>
                <form id="ajax-form" action="{{route('site/verify-account-process')}}" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" name="code" value="{{$code}}">
                    <div class="mb-3">
                        <label class="form-label">OTP <span class="text-danger">*</span></label>
                        <input name="otp" type="number" class="form-control" required maxlength="6" minlength="6" autofocus />
                    </div>
                    <button class="btn btn-primary d-grid w-100 mb-3">Submit</button>
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
        $('#ajax-form').validate({
            submitHandler: function(form) {
                app.ajaxForm(form);
            }
        });
    });

    function resendOtp() {
        app.ajaxPost("{{ route('auth/resend-otp') }}", {
            type: 'verify_account',
            code: '{{$code}}'
        })
    }
</script>
@endpush
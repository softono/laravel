@extends('layouts.blank')
@section('title')
Forgot Password
@endsection
@section('content')
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-4">
            <div class="card">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-4 mt-2">
                        <a href="site/password-forgot" class="app-brand-link d-flex align-items-center">
                            <span class="app-brand-logo demo">
                                <img src="{{$general->getFileUrl(config('setting.app_logo'),'logo')}}" class="brand-image img-circle elevation-3 preview-app-logo " style="height: 50px;">
                            </span>
                            <span class="app-brand-text demo text-body fw-bold ms-1">{{ Config::get('setting.app_name') }}</span>
                        </a>
                    </div>
                    <!-- /Logo -->
                    <h4 class="mb-1">Forgot Password? 🔒</h4>
                    <p class="mb-6">Enter your email and we'll send you instructions to reset your password</p>
                    {{ view('common/message_alert') }}
                    <form id="ajax-form" action="{{ route('site/password-forgot-process') }}" class="mb-6 fv-plugins-bootstrap5 fv-plugins-framework" method="POST">
                        {{ csrf_field() }}
                        <input type="hidden" name="step" id="step" value="1">
                        <div id="email-block">
                            <div class="mb-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input id="email" type="email" class="form-control" name="email" placeholder="Enter your email" autofocus value="" />
                                <div class="col-12 recaptcha-block">
                                    {{view('common/recaptcha')}}
                                </div>
                            </div>
                        </div>
                        <div id="otp-block" style="display: none;">
                            <div class="mb-6">
                                <label class="form-label">OTP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="otp" placeholder="Enter your otp" autofocus value="" />
                                <div class="text-center">
                                    <br>
                                    Didn't get the code?
                                    <a href="javascript:void(0)" onclick="resendOtp($('#email').val())" id="resend-otp-link">Resend</a>
                                </div>
                            </div>
                        </div>
                        <div id="password-block" style="display: none;">
                            <div class="mb-6">
                                <div class="mb-6 form-password-toggle form-control-validation">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-merge has-validation">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" autofocus value="" />
                                    <span class="input-group-text cursor-pointer">
                                        <i class="icon-base bx bx-hide"></i></span>
                                </div>
                                <label id="password-error" class="error" for="password" style="display:none;"></label>
                                </div>
                                <div class="mb-6 form-password-toggle form-control-validation">
                                    <label class="form-label">confirm password <span class="text-danger">*</span></label>
                                     <div class="input-group input-group-merge has-validation">
                                        <input type="password" class="form-control" name="password_confirm" id="password_confirm" placeholder="Enter your password" value="" />
                                         <span class="input-group-text cursor-pointer">
                                              <i class="icon-base bx bx-hide"></i></span>
                                    </div>
                                 <label id="password_confirm-error" class="error" for="password_confirm" style="display:none;"></label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary d-grid w-100 waves-effect waves-light">Submit</button>
                    </form>
                   
                    <div class="text-center">
                        <a href="{{ route('login') }}" class="d-flex justify-content-center pjax">
                            <i class="icon-base bx bx-chevron-left me-1"></i>
                            Back to login
                        </a>
                    </div>
                </div>
                <!-- /Forgot Password -->
            </div>
        </div>
    </div>
    <!-- /.login-box -->
    @endsection
    @push('scripts')
    <script>
        documentReady(function() {
            $('#ajax-form').validate({
                rules: {
                    email: {
                        required: true,
                        email: true
                    },
                },
                messages: {
                    email: {
                        required: "Please Enter Your Email.",
                        email: "Please enter a valid email address."
                    },
                },
                submitHandler: function(form) {
                    app.ajaxForm(form, function(response) {
                        if ($('#step').val() == '1') {
                            try {
                                grecaptcha.reset();
                            } catch (e) {}
                        }
                        if (response.status) {
                            if (response.message) {
                                app.showMessageWithCallback(response.message, "success").then(function() {
                                    if (response.next == 'redirect') {
                                        window.location.href = response.url;
                                    }
                                });
                                if (response.next == 'step_2') {
                                    $('#otp-block').show();
                                    $('#email-block').hide();
                                    $('#step').val('2');
                                } else if (response.next == 'step_3') {
                                    $('#password-block').show();
                                    $('#otp-block').hide();
                                    $('#step').val('3');
                                }
                            }
                        } else if (response.message) {
                            app.showMessage(response.message, "error");
                        }
                    });
                }
            })
        })

        function resendOtp(email) {
            app.ajaxPost("{{ route('auth/resend-otp') }}", {
                type: 'forgot_password',
                code: btoa(email)
            })
        }
    </script>

    @endpush
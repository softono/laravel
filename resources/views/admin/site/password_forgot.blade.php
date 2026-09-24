@extends('layouts.blank')
@section('title')
Forgot Password
@endsection
@section('content')

<div class="w-full max-w-md">
    <div class="card">
        <div class="card-body p-6 sm:p-8">
            <!-- Logo -->
            <div class="mb-6 flex justify-center">
                <a href="admin/site/password-forgot" class="flex items-center gap-2 pjax">
                    <img src="{{$general->getFileUrl(config('setting.app_logo'))}}" class="h-10 w-10 rounded-full object-cover" alt="">
                    <span class="text-lg font-bold text-slate-800">{{ Config::get('setting.app_name') }}</span>
                </a>
            </div>
            <!-- /Logo -->
            <h4 class="mb-1 text-xl font-semibold text-slate-800">Forgot Password? 🔒</h4>
            <p class="mb-6 text-sm text-slate-500">Enter your email and we'll send you instructions to reset your password</p>
            {{ view('common/message_alert') }}
            <form id="ajax-form" action="{{ route('admin/site/password-forgot-process') }}" class="mb-6" method="POST">
                {{ csrf_field() }}
                <input type="hidden" name="step" id="step" value="1">
                <div id="email-block">
                    <div class="mb-6">
                        <label class="form-label">Email <span class="text-rose-600">*</span></label>
                        <input id="email" type="email" class="form-input" name="email" placeholder="Enter your email" autofocus value="" />
                        <div class="mt-3">
                            {{view('common/recaptcha')}}
                        </div>
                    </div>
                </div>
                <div id="otp-block" style="display: none;">
                    <div class="mb-6">
                        <label class="form-label">OTP <span class="text-rose-600">*</span></label>
                        <input type="text" class="form-input" name="otp" placeholder="Enter your otp" autofocus value="" />
                        <div class="text-center">
                            <br>
                            Didn't get the code?
                            <a href="javascript:void(0)" onclick="resendOtp($('#email').val())" id="resend-otp-link">Resend</a>
                        </div>
                    </div>
                </div>
                <div id="password-block" style="display: none;">
                    <div class="mb-6">
                        <div class="mb-6">
                            <label class="form-label">Password <span class="text-rose-600">*</span></label>
                                <div class="input-group">
                                     <input type="password" class="form-input" id="password" name="password" placeholder="Enter your password" autofocus value="" />
                                     <span class="input-group-text cursor-pointer">
                                     <i class="bx bx-hide"></i></span>
                                </div>
                                 <label id="password-error" class="error" for="password" style="display:none;"></label>
                        </div>
                        <div class="mb-6">
                            <label class="form-label">confirm password <span class="text-rose-600">*</span></label>
                                 <div class="input-group">
                                    <input type="password" class="form-input" name="password_confirm" id="password_confirm" placeholder="Enter your password" value="" />
                                     <span class="input-group-text cursor-pointer">
                                         <i class="bx bx-hide"></i></span>
                                </div>
                                 <label id="password_confirm-error" class="error" for="password_confirm" style="display:none;"></label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-primary w-full">Submit</button>
            </form>
            <div class="text-center">
                <a href="{{ route('admin/auth/login') }}" class="inline-flex items-center justify-center gap-1 text-sm text-primary-600 hover:underline pjax">
                    <i class="bx bx-chevron-left rtl:-scale-x-100"></i>
                    Back to login
                </a>
            </div>
        </div>
        <!-- /Forgot Password -->
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
                            app.showMessageWithCallback(response.message,"success").then(function(){
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
            },
              highlight: function(element) {
            $(element).addClass('is-invalid');
            $(element)
                .closest('.input-group')
                .find('.input-group-text')
                .addClass('error');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid');

            $(element)
                .closest('.input-group')
                .find('.input-group-text')
                .removeClass('error');
        },
        errorPlacement: function(error, element) {
            if ($(element).closest('.input-group').length) {
                error.insertAfter($(element).closest('.input-group'));
            } else {
                error.insertAfter(element);
            }
        }
        })
    })

    function resendOtp(email) {
        app.ajaxPost("{{ route('admin/auth/resend-otp') }}", {
            type: 'forgot_password',
            code: btoa(email)
        })
    }
</script>

@endpush

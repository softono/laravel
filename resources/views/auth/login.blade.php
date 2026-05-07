@extends('layouts.blank')
@section('title')
    Login To Your Account
@endsection
@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-6">
                <!-- Login -->
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center">
                            <a href="admin/auth/login" class="app-brand-link d-flex align-items-center pjax">
                                <span class="app-brand-logo demo">
                                    <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                                        class="brand-image img-circle elevation-3 preview-app-logo " style="height: 50px;">
                                </span>
                                <span
                                    class="app-brand-text demo text-heading fw-bold">{{ Config::get('setting.app_name') }}</span>
                            </a>
                        </div>
                        <!-- /Logo -->
                        <h4 class="mb-1">Welcome to {{ Config::get('setting.app_name') }} 👋</h4>
                        <p class="mb-6">Please Log-in to your account</p>
                        <form id="login-form" class="mb-4" action="{{ route('auth/login-process') }}" method="POST">
                            @csrf
                            <div class="mb-6">
                                <label for="email" class="form-label">Email or Phone <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="login-email" name="email"
                                    placeholder="Enter your Email or Phone" autofocus />
                                <label id="email-error" class="error" for="email" style="display:none;"></label>
                            </div>
                            <div class="mb-6 form-password-toggle form-control-validation">
                                <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                                <div class="input-group input-group-merge has-validation">
                                    <input type="password" id="password" class="form-control" name="password"
                                        aria-describedby="password" />
                                    <span class="input-group-text cursor-pointer "><i
                                            class="icon-base bx bx-hide"></i></span>
                                </div>
                                <label id="password-error" class="error" for="password" style="display:none;"></label>
                            </div>
                            <div class="my-8">
                                <div class="d-flex justify-content-between">
                                    <div class="form-check mb-0 ms-2">
                                        <input class="form-check-input" type="checkbox" id="remember-me" name="remember"
                                            checked />
                                        <label class="form-check-label" for="remember-me"> Remember Me </label>
                                    </div>
                                    <a href="site/password-forgot" class="pjax">
                                        <p class="mb-0">Forgot Password?</p>
                                    </a>
                                </div>
                            </div>

                            <div class="mb-6">
                                <button class="btn btn-primary d-grid w-100 mb-4" type="submit">Login</button>
                                @if (Config::get('setting.user_login_with_otp') == 1)
                                    <button type="button" class="btn btn-primary d-grid w-100 mb-4"
                                        onclick="$('#login-otp-form').show(); $('#login-form').hide();">
                                        Login with OTP
                                    </button>
                                @endif
                            </div>
                        </form>
                        <form id="login-otp-form" action="{{ route('auth/login-otp-process') }}" method="POST"
                            style="display: none;">
                            {{ csrf_field() }}
                            <input type="hidden" name="step" id="step" value="1">
                            <div id="email-block">
                                <div class="mb-6 ">
                                    <label for="email" class="form-label">Email <span
                                            class="text-danger">*</span></label>
                                    <input type="email" required class="form-control" id="otp-email" name="email"
                                        placeholder="Enter your email" autofocus value="{{ old('email') }}" />
                                    <div class="col-12">
                                        {{ view('common/recaptcha') }}
                                    </div>
                                </div>
                            </div>
                            <div id="otp-block" style="display: none;">
                                <div class="mb-6">
                                    <label class="form-label">OTP <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="otp" placeholder="Enter your otp"
                                        autofocus value="" />
                                </div>
                            </div>
                            <div class="mb-6">
                                <button type="submit" class="btn btn-primary d-grid w-100 mb-4">Submit</button>
                                <button type="button" class="btn btn-primary d-grid w-100 mb-4"
                                    onclick="$('#login-form').show();$('#login-otp-form').hide();">Login with
                                    Password</button>
                            </div>
                        </form>
                        <p class="text-center">
                            <span>New on our platform?</span>
                            <a href="register" class="pjax">
                                <span>Create an account</span>
                            </a>
                        </p>
                    </div>
                </div>
                <!-- /Register -->
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {

            $('#login-form').validate({

                rules: {
                    email: {
                        required: true,
                    },
                    password: {
                        required: true,
                    },
                },

                messages: {
                    email: {
                        required: "Please Enter Your Email/Phone.",
                    },
                    password: {
                        required: "Please Enter Your Password",
                    }
                },

                submitHandler: function(form) {

                    event.preventDefault();

                    app.ajaxForm(form, function(response) {

                        if (response.status == 1) {

                            // ✅ ADD THIS BLOCK
                            let cookieName = "{{ config('setting.app_uid') }}_token";

                            let deviceToken = document.cookie.match(new RegExp(cookieName +
                                "=([^;]+)"))?.[1];

                            if (!deviceToken) {
                                deviceToken = btoa(String.fromCharCode(...crypto
                                    .getRandomValues(new Uint8Array(32))));
                                document.cookie = cookieName + "=" + deviceToken +
                                    "; path=/; max-age=31536000; SameSite=Lax";
                            }

                            // redirect AFTER cookie set
                            window.location.href = response.url;

                        } else {

                            app.showMessage(response.message, "error");

                        }

                    });

                    return false;
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

            });



            $('#login-otp-form').validate({

                submitHandler: function(form) {

                    app.ajaxForm(form, function(response) {

                        if ($('#step').val() == '1') {
                            try {
                                grecaptcha.reset();
                            } catch (e) {}
                        }

                        if (response.status) {

                            if (response.message) {

                                app.showConfirmationPopup({
                                    title: "",
                                    text: response.message,
                                    type: "success"
                                }).then(function() {

                                    if (response.next == 'step_2') {
                                        $('#otp-block').show();
                                        $('#email-block').hide();
                                        $('#step').val('2');
                                    }

                                    if (response.next == 'redirect') {
                                        window.location.href = response.url;
                                    }

                                });

                            }

                        } else if (response.message) {

                            app.showMessage(response.message, "error");

                        }

                    });

                }

            });

        });
    </script>
@endpush

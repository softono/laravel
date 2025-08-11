@extends('layouts.main')
@section('title')
Change password
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">
    {{ view('account/component/account_block',compact('model')) }}
    <div class="card mb-6">
        <h5 class="card-header">Change Password</h5>
        <div class="card-body">
            <form action="account/password-change-process" method="post" id="ajax-form" class="fv-plugins-bootstrap5 fv-plugins-framework">
                {{ csrf_field() }}
                <div class="row">
                    <div class="mb-6 col-md-6 form-password-toggle form-control-validation fv-plugins-icon-container">
                        <label class="form-label" for="currentPassword">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group input-group-merge has-validation">
                            <input class="form-control" maxlength="32" minlength="6" type="password" name="current_password" id="current_password" required="required" />
                             <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                        </div>
                        <label id="current_password-error" class="error" for="current_password" style="display:none;"></label>
                    </div>
                </div>
                    <div class="row">
                        <div class="mb-6 col-md-6 form-password-toggle form-control-validation fv-plugins-icon-container fv-plugins-bootstrap5-row-invalid">
                            <label class="form-label" for="newPassword">New Password <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge has-validation">
                                <input class="form-control" maxlength="32" minlength="6" type="password" id="password" name="password" required="required" />
                                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                            </div>
                            <label id="password-error" class="error" for="password" style="display:none;"></label>
                        </div>
                        <div class="mb-6 col-md-6 form-password-toggle form-control-validation fv-plugins-icon-container fv-plugins-bootstrap5-row-invalid">
                            <label class="form-label" for="confirmPassword">Confirm New Password <span class="text-danger">*</span></label>
                            <div class="input-group input-group-merge has-validation">
                                <input class="form-control" maxlength="32" minlength="6" type="password" name="confirm_password" id="confirm_password" required="required" />
                                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                            </div>
                            <label id="confirm_password-error" class="error" for="confirm_password" style="display:none;"></label>
                        </div>
                        <h6 class="text-body">Password Requirements:</h6>
                        <ul class="ps-8 mb-0">
                            <li class="mb-4">Password must be at least 6 characters long.</li>
                        </ul>
                        <div class="mt-6">
                            <button type="submit" class="btn btn-primary me-2">Save changes</button>
                            <button type="reset" class="btn btn-dark">Cancel</button>
                        </div>
                    </div>
            </form>
        </div>
    </div>
</div>
</div>

<script type="text/javascript">
    documentReady(function() {
        $('#ajax-form').validate({
            submitHandler: function(form) {
                app.ajaxForm(form);
            },
             rules: {
                title: {
                    required: true,
                },
                keyword: {
                    required: true,
                },

            },
            messages: {
                current_password: {
                    required: "Please enter the current password",
                },
                password: {
                    required: "Please enter the password",
                },
                confirm_password: {
                    required: "Please enter the confirm password",
                },
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
    });
</script>
@endsection
@extends($layout)
@section('title')
    Change password
@endsection
@section('content')
    <div>
        @include('modules.user.account.component.account_block')
        <div class="card">
            <h5 class="card-header">Change Password</h5>
            <div class="card-body">
                <form action="{{ url('auth/change-password') }}" method="post" id="ajax-form">
                    {{ csrf_field() }}
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="form-label" for="currentPassword">Current Password <span
                                    class="text-rose-600">*</span></label>
                            <div class="input-group">
                                <input class="form-input" maxlength="32" minlength="6" type="password"
                                    name="current_password" id="current_password" required="required" />
                                <span class="input-group-text cursor-pointer"><i
                                        class="bx bx-hide"></i></span>
                            </div>
                            <label id="current_password-error" class="error" for="current_password"
                                style="display:none;"></label>
                        </div>
                    </div>
                    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="form-label" for="newPassword">New Password <span
                                    class="text-rose-600">*</span></label>
                            <div class="input-group">
                                <input class="form-input" maxlength="32" minlength="6" type="password" id="password"
                                    name="password" required="required" />
                                <span class="input-group-text cursor-pointer"><i
                                        class="bx bx-hide"></i></span>
                            </div>
                            <label id="password-error" class="error" for="password" style="display:none;"></label>
                        </div>
                        <div>
                            <label class="form-label" for="confirmPassword">Confirm New Password <span
                                    class="text-rose-600">*</span></label>
                            <div class="input-group">
                                <input class="form-input" maxlength="32" minlength="6" type="password"
                                    name="confirm_password" id="confirm_password" required="required" />
                                <span class="input-group-text cursor-pointer"><i
                                        class="bx bx-hide"></i></span>
                            </div>
                            <label id="confirm_password-error" class="error" for="confirm_password"
                                style="display:none;"></label>
                        </div>
                        <div class="md:col-span-2">
                            <h6 class="text-slate-700">Password Requirements:</h6>
                            <ul class="mb-0 list-disc pl-8">
                                <li class="mb-4">Password must be at least 6 characters long.</li>
                            </ul>
                        </div>
                        <div class="mt-2 md:col-span-2">
                            <button type="submit" class="btn-primary mr-2">Save changes</button>
                            <button type="reset" class="btn-dark">Cancel</button>
                        </div>
                    </div>
                </form>
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

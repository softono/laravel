@extends('layouts.main')
@section('title')
    Profile
@endsection
@section('content')

    <div>
        {{ view('account/component/account_block', compact('model')) }}
        <div class="space-y-4">
            <div class="card">
                <div class="card-body">

                    <div class="flex items-start gap-6 border-b border-slate-200 pb-4 sm:items-center">
                        <img src="{{ $general->getFileUrl($model->image, 'profile') }}" alt="user-avatar"
                            class="block h-[100px] w-[100px] rounded" height="100px" width="100px"
                            id="uploadedAvatar">
                        <div>
                            <a onclick="app.showModalView('account/image')" for="upload"
                                class="btn-primary pjax mb-4 mr-3 text-white" tabindex="0">
                                <span class="hidden sm:block">Upload new photo</span>
                                <i class="bx bx-upload block sm:hidden"></i>
                            </a>
                            <div class="text-sm text-slate-600">Allowed JPG, GIF or PNG.</div>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-4">
                    <form action="account/update-process" method="post" id="ajax-form">
                        {{ csrf_field() }}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <label for="firstName" class="form-label">First Name <span
                                        class="text-rose-600">*</span></label>
                                <input class="form-input" type="text" id="first_name" name="first_name"
                                    value="{{ $model->first_name }}" autofocus="" placeholder="Enter First Name"
                                    required="required" maxlength="128">
                            </div>

                            <div>
                                <label for="lastName" class="form-label">Last Name <span
                                        class="text-rose-600">*</span></label>
                                <input type="text" value="{{ $model->last_name }}" name="last_name" class="form-input"
                                    id="last_name" placeholder="Enter Last Name" required="required" maxlength="128">
                            </div>

                            <div>
                                <label for="email" class="form-label">E-mail <span class="text-rose-600">*</span></label>
                                <input type="email" value="{{ $model->email }}" name="email" class="form-input"
                                    id="email" placeholder="Enter Email" required="required">
                            </div>

                            <div>
                                <label class="form-label" for="phoneNumber">Phone Number <span
                                        class="text-rose-600">*</span></label>
                                <input type="number" value="{{ $model->phone }}" name="phone" class="form-input"
                                    id="phone" placeholder="Enter Phone" required="required">
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="btn-primary mr-3">Save changes</button>
                            <button type="reset" class="btn-dark">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Delete Account</h5>
                </div>
                <div class="card-body">
                    <div class="mb-6">
                        <div class="alert-warning">
                            <h5 class="mb-1 font-semibold">Are you sure you want to delete your account?</h5>
                            <p>Once you delete your account, there is no going back. Please be certain.</p>
                        </div>
                    </div>
                    <form action="{{ route('account/deactivate') }}" id="formAccountDeactivation" method="POST">
                        @csrf
                        <div class="my-8 ml-2 flex items-center gap-2">
                            <input type="checkbox" class="form-check-input" name="accountActivation" id="accountActivation">
                            <label for="accountActivation" class="form-check-label">I confirm my account
                                deactivation</label>
                        </div>
                        <button type="submit" class="btn-danger deactivate-account">Deactivate Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript">
        jQuery.validator.addMethod("noDisposableEmail", v => !["mailinator.com", "tempmail.com", "10minutemail.com",
            "guerrillamail.com", "fakeinbox.com"
        ].includes((v.split('@')[1] || "").toLowerCase()), "Disposable email addresses are not allowed.");
        documentReady(function() {
            $('#ajax-form').validate({
                rules: {
                    email: {
                        required: true,
                        email: true,
                        noDisposableEmail: true
                    },
                },
                messages: {
                    email: {
                        required: "Please enter the email",
                        email: "Please enter a valid email address",
                        noDisposableEmail: "Please enter a valid Email domain"
                    },
                },
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            })
        });
    </script>
@endpush

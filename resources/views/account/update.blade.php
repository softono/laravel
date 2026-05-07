@extends('layouts.main')
@section('title')
    Profile
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            {{ view('account/component/account_block', compact('model')) }}
            <div class="card mb-6">
                <div class="card-body">
                    <div class="d-flex align-items-start align-items-sm-center gap-6 pb-4 border-bottom">
                        <img src="{{ $general->getFileUrl($model->image, 'profile') }}" alt="user-avatar"
                            class="d-block w-px-100 h-px-100 rounded" alt="image" height="100px" width="100px"
                            id="uploadedAvatar">
                        <div class="button-wrapper">
                            <a onclick="app.showModalView('account/image')" for="upload"
                                class="btn btn-primary me-3 mb-4 text-white pjax" tabindex="0">
                                <span class="d-none d-sm-block">Upload new photo</span>
                                <i class="icon-base bx bx-upload d-block d-sm-none"></i>
                            </a>
                            <div>Allowed JPG, GIF or PNG.</div>
                        </div>
                    </div>W
                </div>
                <div class="card-body pt-4">
                    <form action="account/update-process" method="post" id="ajax-form">
                        {{ csrf_field() }}
                        <div class="row g-6">
                            <div class="col-md-6">
                                <label for="firstName" class="form-label">First Name <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" id="first_name" name="first_name"
                                    value="{{ $model->first_name }}" autofocus="" placeholder="Enter First Name"
                                    required="required" maxlength="128">
                            </div>

                            <div class="col-md-6">
                                <label for="lastName" class="form-label">Last Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" value="{{ $model->last_name }}" name="last_name" class="form-control"
                                    id="last_name" placeholder="Enter Last Name" required="required" maxlength="128">
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">E-mail <span class="text-danger">*</span></label>
                                <input type="email" value="{{ $model->email }}" name="email" class="form-control"
                                    id="email" placeholder="Enter Email" required="required">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="phoneNumber">Phone Number <span
                                        class="text-danger">*</span></label>
                                <input type="number" value="{{ $model->phone }}" name="phone" class="form-control"
                                    id="phone" placeholder="Enter Phone" required="required">
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="btn btn-primary me-3">Save changes</button>
                            <button type="reset" class="btn btn-dark">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h5>Delete Account</h5>
                </div>
                <div class="card-body">
                    <div class="mb-6 col-12 mb-0">
                        <div class="alert alert-warning">
                            <h5 class="alert-heading mb-1">Are you sure you want to delete your account?</h5>
                            <p>Once you delete your account, there is no going back. Please be certain.</p>
                        </div>
                    </div>
                    <form action="{{ route('account/deactivate') }}" id="formAccountDeactivation" method="POST">
                        @csrf
                        <div class="form-check my-8 ms-2">
                            <input type="checkbox" class="form-check-input" name="accountActivation" id="accountActivation">
                            <label for="accountActivation" class="form-check-label">I confirm my account
                                deactivation</label>
                        </div>
                        <button type="submit" class="btn btn-danger deactivate-account">Deactivate Account</button>
                    </form>
                </div>
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

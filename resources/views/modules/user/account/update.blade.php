@extends('layouts.main')
@section('title')
    Profile
@endsection
@section('content')

    <div>
        {{ view('modules.user.account.component.account_block', compact('model')) }}
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
                    <form action="{{ route('account/update-process') }}" method="post" id="ajax-form">
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
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" value="{{ $model->email }}" class="form-input" id="email"
                                    readonly disabled>
                            </div>

                            <div>
                                <label class="form-label" for="phone">Phone Number</label>
                                <input type="tel" value="{{ $model->phone }}" name="phone" class="form-input"
                                    id="phone" placeholder="Enter Phone">
                            </div>

                            <div>
                                <label class="form-label" for="country">Country</label>
                                <select name="country" id="country" class="form-select">
                                    <option value="">Select country</option>
                                    @foreach ($countries as $code => $name)
                                        <option value="{{ $code }}" @selected($model->country === $code)>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label" for="timezone">Timezone</label>
                                <select name="timezone" id="timezone" class="form-select">
                                    @foreach ($timezones as $timezone)
                                        <option value="{{ $timezone }}" @selected($model->timezone === $timezone)>{{ $timezone }}</option>
                                    @endforeach
                                </select>
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
                    <h5 class="card-title">Deactivate Account</h5>
                </div>
                <div class="card-body">
                    <div class="mb-6">
                        <div class="alert-warning">
                            <h5 class="mb-1 font-semibold">Are you sure you want to deactivate your account?</h5>
                            <p>Your account will be deactivated and you will be signed out on every device.</p>
                        </div>
                    </div>
                    <form action="{{ route('account/deactivate') }}" id="formAccountDeactivation" method="POST">
                        @csrf
                        <div class="my-8 ml-2 flex items-center gap-2">
                            <input type="checkbox" class="form-check-input" name="accountActivation" id="accountActivation" required>
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
        documentReady(function() {
            $('#ajax-form').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            })
        });
    </script>
@endpush

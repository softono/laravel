<form method="post" action="{{ route('admin/admin/save') }}" enctype="multipart/form-data" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">
    <input type="hidden" name="pass" value="{{ @$model->password }}">
    <input type="hidden" name="role" value="1">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label" for="basic-icon-default-fullname">First Name <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <input type="text" class="form-control" id="first_name" placeholder="First Name"
                                name="first_name" aria-label="first_name" value="{{ @$model->first_name }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label" for="basic-icon-default-fullname">Last Name <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <input type="text" class="form-control" id="last_name" placeholder="Last Name"
                                name="last_name" aria-label="last_name" value="{{ @$model->last_name }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label" for="basic-icon-default-fullname">Email <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <input type="email" class="form-control" id="email" placeholder="Email" name="email"
                                aria-label="Name" value="{{ @$model->email }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Country</label>
                        <select class="form-control" name="country" onchange="getPhoneCode(this)">
                            <option value="">-- Select Country --</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->sortname }}" "
                                    {{ @$model->country == $country->sortname ? 'selected' : '-' }}>
                                    {{ $country->name }}
                                </option>
 @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label" for="basic-icon-default-fullname">Phone Number <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <input type="number" class="form-control" id="phone" placeholder="Phone Number"
                                name="phone" aria-label="Phone" value="{{ @$model->phone }}" />
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3 form-password-toggle">
                        <label class="form-label" for="basic-icon-default-password">Password <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge has-validation">
                            <input type="password" class="form-control" id="password" placeholder="Password"
                                name="password" autocomplete="new-password" value="" />
                            <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                        </div>
                        <label id="password-error" class="error" for="password" style="display:none;"></label>

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label" for="basic-icon-default-fullname">Status</label>
                        <select class="form-select" data-style="btn-default" name="status" aria-label="Status">
                            <option value="1" <?php
                            if (@$model->status == 1) {
                                echo 'selected';
                            } ?>>Active</option>
                            <option value="0" <?php if (@$model->status == 0) {
                                echo 'selected';
                            } ?>>Inactive</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="col-md-6"><br>
        @if (!empty($model->image))
            <img src="{{ $general->getFileUrl($model->image, 'profile') }}" style="width:100px;height:100px"
                class="img-fluid" id="image"><br>
        @else
            <img src="{{ $general->getNoFile() }}" class="img-fluid" style="width:100px;height:100px"
                id="image"><br>
        @endif
        <div class="form-group">
            <label class="form-label">Image<span class="text-danger">*</span></label><br><br>
            <div class="custom-file">
                <input type="file" class="form-control custom-file-input" accept="image/*" name="image"
                    onchange="previewImage(this,'#image')"><br>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="mb-3">
            <h5 class="fw-medium" for="basic-icon-default-fullname">Permission <span class="text-danger">*</span>
            </h5>
        </div>
        @php
            $permissions = collect(explode(',', $model->permission ?? ''))
                ->map(fn($item) => strtolower(trim($item)))
                ->toArray();
        @endphp
        <div class="row">
            @foreach ($model->getPermissionListData() as $permissionList)
                <div class="col-md-3 mb-3">
                    <div class="form-check custom-option custom-option-basic">

                        <label class="form-check-label custom-option-content checkbox-block" for="customCheckTemp4">
                            <input
                                onchange="$(this).closest('.checkbox-block').find('.checkbox-child').prop('checked',this.checked);"
                                class="form-check-input checkbox-parent ms-0 " type="checkbox" name="permission[]"
                                value="{{ $permissionList['key'] }}"
                                @if (in_array(strtolower($permissionList['key']), $permissions)) checked @endif />
                            <span class="custom-option-header">
                                <span class="h5 mb-0 ps-2">{{ $permissionList['title'] }}</span>
                            </span>
                            <span class="custom-option-body">
                                <small>
                                    @if (isset($permissionList['list']) && $permissionList['list'])
                                        @foreach ($permissionList['list'] as $permission)
                                            <div class="form-check">
                                                <div class="ps-1">
                                                    <input
                                                        onchange="if(this.checked){$(this).closest('.checkbox-block').find('.checkbox-parent').prop('checked',true);}"
                                                        class="form-check-input checkbox-child" type="checkbox"
                                                        name="permission[]" value="{{ $permission['key'] }}"
                                                        @if (in_array(strtolower($permission['key']), $permissions)) checked @endif />
                                                    <label class="form-check-label" for="defaultCheck3">
                                                        {{ $permission['title'] }}
                                                </div>
                        </label>
                    </div>
            @endforeach
            @endif
            </small>
            </span>
            </label>
        </div>
    </div>
    @endforeach
    </div>

    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
    <a href="admin/admin" class="btn btn-dark pjax" style="color: white">Back</a>
</form>

@push('scripts')
    <script type="text/javascript">
        jQuery.validator.addMethod("noDisposableEmail", v => !["mailinator.com", "tempmail.com", "10minutemail.com",
            "guerrillamail.com", "fakeinbox.com"
        ].includes((v.split('@')[1] || "").toLowerCase()), "Disposable email addresses are not allowed.");
        documentReady(function() {
            // Add a custom method to validate alphabetic characters
            jQuery.validator.addMethod("alphaOnly", function(value, element) {
                return this.optional(element) || /^[a-zA-Z\s]+$/.test(value);
            }, "Please enter only alphabetic characters");
            $('#ajax-form').validate({
                rules: {
                    first_name: {
                        required: true,
                        alphaOnly: true,
                        minlength: 2
                    },
                    last_name: {
                        required: true,
                        alphaOnly: true,
                        minlength: 2
                    },
                    email: {
                        required: true,
                        email: true,
                        noDisposableEmail: true
                    },
                    status: {
                        required: true,
                    },
                    phone: {
                        required: true,
                        minlength: 10
                    },

                },
                messages: {
                    first_name: {
                        required: "Please enter the first name",
                        minlength: "Please enter at least 2 characters"
                    },
                    last_name: {
                        required: "Please enter the last name",
                        minlength: "Please enter at least 2 characters"
                    },
                    email: {
                        required: "Please enter the email",
                        email: "Please enter a valid email address",
                        noDisposableEmail: "Please enter a valid Email domain"
                    },

                    status: {
                        required: "Please select the status",
                    },
                    phone: {
                        required: "Please enter the phone number",
                        minlength: "Please enter at least 10 digits"
                    },
                },

                submitHandler: function(form) {
                    app.ajaxFileForm(form);
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
                        error.insertAfter(element.closest('.mb-3'));
                    }
                    // Place the error message under the input field
                    // error.insertAfter(element.closest('.mb-3'));
                },
            });
        });
    </script>
@endpush

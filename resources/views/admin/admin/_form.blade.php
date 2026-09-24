<form method="post" action="{{ route('admin/admin/save') }}" enctype="multipart/form-data" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">
    <input type="hidden" name="pass" value="{{ @$model->password }}">
    <input type="hidden" name="role" value="1">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="form-label" for="first_name">First Name <span
                    class="text-rose-500">*</span></label>
            <div class="input-group">
                <input type="text" class="form-input" id="first_name" placeholder="First Name"
                    name="first_name" aria-label="first_name" value="{{ @$model->first_name }}" />
            </div>
        </div>
        <div>
            <label class="form-label" for="last_name">Last Name <span
                    class="text-rose-500">*</span></label>
            <div class="input-group">
                <input type="text" class="form-input" id="last_name" placeholder="Last Name"
                    name="last_name" aria-label="last_name" value="{{ @$model->last_name }}" />
            </div>
        </div>
        <div>
            <label class="form-label" for="email">Email <span
                    class="text-rose-500">*</span></label>
            <div class="input-group">
                <input type="email" class="form-input" id="email" placeholder="Email" name="email"
                    aria-label="Name" value="{{ @$model->email }}" />
            </div>
        </div>
        <div>
            <label class="form-label">Country</label>
            <select class="form-select" name="country" onchange="getPhoneCode(this)">
                <option value="">-- Select Country --</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->sortname }}"
                        {{ @$model->country == $country->sortname ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label" for="phone">Phone Number <span
                    class="text-rose-500">*</span></label>
            <div class="input-group">
                <input type="number" class="form-input" id="phone" placeholder="Phone Number"
                    name="phone" aria-label="Phone" value="{{ @$model->phone }}" />
            </div>
        </div>
        <div>
            <label class="form-label" for="password">Password <span
                    class="text-rose-500">*</span></label>
            <div class="input-group">
                <input type="password" class="form-input" id="password" placeholder="Password"
                    name="password" autocomplete="new-password" value="" />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
            <label id="password-error" class="error" for="password" style="display:none;"></label>
        </div>
        <div>
            <label class="form-label" for="status">Status</label>
            <select class="form-select" name="status" aria-label="Status">
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

    <div class="mt-4">
        @if (!empty($model->image))
            <img src="{{ $general->getFileUrl($model->image, 'profile') }}" style="width:100px;height:100px"
                class="rounded" id="image"><br>
        @else
            <img src="{{ $general->getNoFile() }}" class="rounded" style="width:100px;height:100px"
                id="image"><br>
        @endif
        <div class="mt-3">
            <label class="form-label">Image<span class="text-rose-500">*</span></label>
            <input type="file" class="form-input" accept="image/*" name="image"
                onchange="previewImage(this,'#image')">
        </div>
    </div>
    <div class="mt-4">
        <div class="mb-3">
            <h5 class="font-medium text-slate-800">Permission <span class="text-rose-500">*</span>
            </h5>
        </div>
        @php
            $permissions = collect(explode(',', $model->permission ?? ''))
                ->map(fn($item) => strtolower(trim($item)))
                ->toArray();
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($model->getPermissionListData() as $permissionList)
                <div class="checkbox-block rounded-md border border-slate-200 p-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            onchange="$(this).closest('.checkbox-block').find('.checkbox-child').prop('checked',this.checked);"
                            class="form-check-input checkbox-parent" type="checkbox" name="permission[]"
                            value="{{ $permissionList['key'] }}"
                            @if (in_array(strtolower($permissionList['key']), $permissions)) checked @endif />
                        <span class="text-sm font-semibold text-slate-800">{{ $permissionList['title'] }}</span>
                    </label>
                    @if (isset($permissionList['list']) && $permissionList['list'])
                        <div class="mt-2 pl-1 space-y-1.5">
                            @foreach ($permissionList['list'] as $permission)
                                <div class="flex items-center gap-2">
                                    <input
                                        onchange="if(this.checked){$(this).closest('.checkbox-block').find('.checkbox-parent').prop('checked',true);}"
                                        class="form-check-input checkbox-child" type="checkbox"
                                        name="permission[]" value="{{ $permission['key'] }}"
                                        @if (in_array(strtolower($permission['key']), $permissions)) checked @endif />
                                    <label class="form-check-label text-xs">
                                        {{ $permission['title'] }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    <div class="mt-4 flex gap-2">
        <button type="submit" class="btn-primary">Submit</button>
        <a href="admin/admin" class="btn-dark pjax">Back</a>
    </div>
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
                        error.insertAfter(element.closest('div'));
                    }
                    // Place the error message under the input field
                    // error.insertAfter(element.closest('.mb-3'));
                },
            });
        });
    </script>
@endpush

<form method="post" action="{{ route('admin/user/save') }}" enctype="multipart/form-data" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">
    <input type="hidden" name="pass" value="{{ @$model->password }}">
    <input type="hidden" name="role" value="4">
    <div class="flex flex-wrap gap-4">
        <div class="w-full md:w-1/2">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-ui.label class="mb-2" for="basic-icon-default-first-name">First Name <span
                            class="text-destructive">*</span></x-ui.label>
                    <x-ui.input type="text" id="basic-icon-default-first-name"
                            placeholder="First Name" name="first_name" aria-label="first_name"
                            value="{{ @$model->first_name }}" />
                </div>
                <div>
                    <x-ui.label class="mb-2" for="basic-icon-default-last-name">Last Name <span
                            class="text-destructive">*</span></x-ui.label>
                    <x-ui.input type="text" id="basic-icon-default-last-name"
                            placeholder="Last Name" name="last_name" aria-label="Name"
                            value="{{ @$model->last_name }}" />
                </div>
                <div>
                    <x-ui.label class="mb-2" for="basic-icon-default-fullname">Email <span
                            class="text-destructive">*</span></x-ui.label>
                    <x-ui.input type="email" id="basic-icon-default-fullname"
                            placeholder="Email" name="email" aria-label="Name" value="{{ @$model->email }}" />
                </div>
                <div>
                    <x-ui.label class="mb-2" for="password">Password <span class="text-destructive">*</span></x-ui.label>
                    <x-ui.password-input id="password" placeholder="Password" name="password" autocomplete="password" value="{{ @$model->password }}" />
                    <label id="password-error" class="error" for="password" style="display:none;"></label>
                </div>

                <div>
                    <x-ui.label class="mb-2" for="basic-icon-default-fullname">Status <span
                            class="text-destructive">*</span></x-ui.label>
                    <x-ui.select name="status" aria-label="Status">
                        <option value="1" <?php if (@$model->status == 1) {
                            echo 'selected';
                        } ?>>Active</option>
                        <option value="0" <?php if (@$model->status == 0) {
                            echo 'selected';
                        } ?>>Inactive</option>
                    </x-ui.select>
                </div>
                <div>
                    <x-ui.label class="mb-2">Country</x-ui.label>
                    <x-ui.select name="country" onchange="getPhoneCode(this)">
                        <option value="">-- Select Country --</option>
                        @foreach ($countrilist as $country)
                            <option value="{{ $country->sortname }}"
                                {{ @$model->country == $country->sortname ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </x-ui.select>
                </div>
                <div>
                    <x-ui.label class="mb-2" for="basic-icon-default-phone-number">Phone Number <span
                            class="text-destructive">*</span></x-ui.label>
                    <x-ui.input type="number" id="basic-icon-default-phone-number"
                            placeholder="Phone Number" name="phone" aria-label="Name"
                            value="{{ @$model->phone }}" />
                </div>
            </div>

        </div>
        <div class="w-full md:w-[calc(50%-1rem)]">
            <?php if (!empty($model->image)) { ?>
            <img src="{{ $general->getFileUrl($model->image, 'profile') }}" style="width:100px;height:100px"
                class="rounded" id="image"><br>
            <?php } else { ?>
            <img src="{{ $general->getNoFile() }}" class="rounded" style="width:100px;height:100px"
                id="image"><br>
            <?php  } ?>
            <div class="mt-3">
                <x-ui.label class="mb-2">Image</x-ui.label>
                <x-ui.input type="file" accept="image/*" name="image"
                    onchange="previewImage(this,'#image')" />
            </div>
        </div>

    </div>


    <div class="mt-4 flex gap-2">
        <x-ui.button type="submit">Submit</x-ui.button>
        <x-ui.button variant="secondary" href="admin/user" class="pjax">Back</x-ui.button>
    </div>
</form>

@push('scripts')
    <script type="text/javascript">
        jQuery.validator.addMethod("noDisposableEmail", v => !["mailinator.com", "tempmail.com", "10minutemail.com",
            "guerrillamail.com", "fakeinbox.com"
        ].includes((v.split('@')[1] || "").toLowerCase()), "Disposable email addresses are not allowed.");
        documentReady(function() {
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
                    password: {
                        required: true,
                        minlength: 6,
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
                    password: {
                        required: "Please enter the password",
                        minlength: "Please enter at least 6 characters"
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
            });
        });
    </script>
@endpush

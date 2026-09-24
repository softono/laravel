<form method="post" action="{{ route('admin/admin/save') }}" enctype="multipart/form-data" id="ajax-form">
    @csrf
    <input type="hidden" name="id" value="{{ @$model->id }}">
    <input type="hidden" name="pass" value="{{ @$model->password }}">
    <input type="hidden" name="role" value="1">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <x-ui.label class="mb-2" for="first_name">First Name <span
                    class="text-destructive">*</span></x-ui.label>
            <x-ui.input type="text" id="first_name" placeholder="First Name"
                    name="first_name" aria-label="first_name" value="{{ @$model->first_name }}" />
        </div>
        <div>
            <x-ui.label class="mb-2" for="last_name">Last Name <span
                    class="text-destructive">*</span></x-ui.label>
            <x-ui.input type="text" id="last_name" placeholder="Last Name"
                    name="last_name" aria-label="last_name" value="{{ @$model->last_name }}" />
        </div>
        <div>
            <x-ui.label class="mb-2" for="email">Email <span
                    class="text-destructive">*</span></x-ui.label>
            <x-ui.input type="email" id="email" placeholder="Email" name="email"
                    aria-label="Name" value="{{ @$model->email }}" />
        </div>
        <div>
            <x-ui.label class="mb-2">Country</x-ui.label>
            <x-ui.select name="country" onchange="getPhoneCode(this)">
                <option value="">-- Select Country --</option>
                @foreach ($countries as $country)
                    <option value="{{ $country->sortname }}"
                        {{ @$model->country == $country->sortname ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach
            </x-ui.select>
        </div>
        <div>
            <x-ui.label class="mb-2" for="phone">Phone Number <span
                    class="text-destructive">*</span></x-ui.label>
            <x-ui.input type="number" id="phone" placeholder="Phone Number"
                    name="phone" aria-label="Phone" value="{{ @$model->phone }}" />
        </div>
        <div>
            <x-ui.label class="mb-2" for="password">Password <span
                    class="text-destructive">*</span></x-ui.label>
            <x-ui.password-input id="password" placeholder="Password"
                    name="password" autocomplete="new-password" value="" />
            <label id="password-error" class="error" for="password" style="display:none;"></label>
        </div>
        <div>
            <x-ui.label class="mb-2" for="status">Status</x-ui.label>
            <x-ui.select name="status" aria-label="Status">
                <option value="1" <?php
                if (@$model->status == 1) {
                    echo 'selected';
                } ?>>Active</option>
                <option value="0" <?php if (@$model->status == 0) {
                    echo 'selected';
                } ?>>Inactive</option>
            </x-ui.select>
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
            <x-ui.label class="mb-2">Image<span class="text-destructive">*</span></x-ui.label>
            <x-ui.input type="file" accept="image/*" name="image"
                onchange="previewImage(this,'#image')" />
        </div>
    </div>
    <div class="mt-4">
        <div class="mb-3">
            <h5 class="font-medium text-foreground">Permission <span class="text-destructive">*</span>
            </h5>
        </div>
        @php
            $permissions = collect(explode(',', $model->permission ?? ''))
                ->map(fn($item) => strtolower(trim($item)))
                ->toArray();
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($model->getPermissionListData() as $permissionList)
                <div class="checkbox-block rounded-md border border-border p-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <x-ui.checkbox
                            onchange="$(this).closest('.checkbox-block').find('.checkbox-child').prop('checked',this.checked);"
                            class="checkbox-parent" name="permission[]"
                            value="{{ $permissionList['key'] }}"
                            :checked="in_array(strtolower($permissionList['key']), $permissions)" />
                        <span class="text-sm font-semibold text-foreground">{{ $permissionList['title'] }}</span>
                    </label>
                    @if (isset($permissionList['list']) && $permissionList['list'])
                        <div class="mt-2 pl-1 space-y-1.5">
                            @foreach ($permissionList['list'] as $permission)
                                <div class="flex items-center gap-2">
                                    <x-ui.checkbox
                                        onchange="if(this.checked){$(this).closest('.checkbox-block').find('.checkbox-parent').prop('checked',true);}"
                                        class="checkbox-child" name="permission[]" value="{{ $permission['key'] }}"
                                        :checked="in_array(strtolower($permission['key']), $permissions)" />
                                    <x-ui.label class="font-normal text-xs">
                                        {{ $permission['title'] }}
                                    </x-ui.label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    <div class="mt-4 flex gap-2">
        <x-ui.button type="submit">Submit</x-ui.button>
        <x-ui.button variant="secondary" href="admin/admin" class="pjax">Back</x-ui.button>
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
            });
        });
    </script>
@endpush

{{-- Shared create/update form for end users and admin accounts. $action is the save route, $back the list route. --}}
<form method="post" action="{{ route($action) }}" enctype="multipart/form-data" id="ajax-form" class="space-y-6"
    data-next="load" data-next-url="{{ route($back) }}">
    @csrf
    <input type="hidden" name="id" value="{{ $model->id ?? '' }}">

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="space-y-2">
            <x-ui.label for="first_name">First Name <span class="text-destructive">*</span></x-ui.label>
            <x-ui.input id="first_name" name="first_name" placeholder="First Name" :value="$model->first_name ?? ''" />
        </div>
        <div class="space-y-2">
            <x-ui.label for="last_name">Last Name <span class="text-destructive">*</span></x-ui.label>
            <x-ui.input id="last_name" name="last_name" placeholder="Last Name" :value="$model->last_name ?? ''" />
        </div>
        <div class="space-y-2">
            <x-ui.label for="email">Email <span class="text-destructive">*</span></x-ui.label>
            <x-ui.input type="email" id="email" name="email" placeholder="Email" :value="$model->email ?? ''" />
        </div>
        <div class="space-y-2">
            <x-ui.label for="password">Password @unless (isset($model))<span class="text-destructive">*</span>@endunless</x-ui.label>
            <x-ui.input type="password" id="password" name="password" autocomplete="new-password"
                :placeholder="isset($model) ? 'Leave blank to keep the current password' : 'Password'" />
        </div>
        <div class="space-y-2">
            <x-ui.label for="status">Status <span class="text-destructive">*</span></x-ui.label>
            <x-ui.select id="status" name="status">
                @foreach ([\App\Constants\UserStatus::ACTIVE => 'Active', \App\Constants\UserStatus::INACTIVE => 'Inactive'] as $value => $label)
                    <option value="{{ $value }}" @selected(($model->status ?? \App\Constants\UserStatus::ACTIVE) === $value)>{{ $label }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="space-y-2">
            <x-ui.label for="country">Country</x-ui.label>
            <x-ui.select id="country" name="country">
                <option value="">Select country</option>
                @foreach ($countries as $code => $name)
                    <option value="{{ $code }}" @selected(($model->country ?? null) === $code)>{{ $name }}</option>
                @endforeach
            </x-ui.select>
        </div>
        <div class="space-y-2">
            <x-ui.label for="phone">Phone Number</x-ui.label>
            <x-ui.input type="tel" id="phone" name="phone" placeholder="Phone Number" :value="$model->phone ?? ''" />
        </div>
        <div class="space-y-2">
            <x-ui.label for="image">Image</x-ui.label>
            <div class="flex items-center gap-3">
                <img src="{{ ! empty($model->image) ? $general->getFileUrl($model->image, 'profile') : $general->getNoFile() }}"
                    class="size-12 rounded-md border object-cover" id="image-preview" alt="">
                <x-ui.input type="file" accept="image/*" name="image" onchange="previewImage(this,'#image-preview')" />
            </div>
        </div>
    </div>

    @isset($permissionGroups)
        <div class="space-y-3">
            <x-ui.label>Permission</x-ui.label>
            @php($granted = array_filter(explode(',', $model->permission ?? '')))
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($permissionGroups as $group)
                    <x-ui.card class="gap-3 py-4">
                        <x-ui.card-header>
                            <x-ui.card-title>{{ $group['title'] }}</x-ui.card-title>
                        </x-ui.card-header>
                        <x-ui.card-content class="space-y-2">
                            @foreach ($group['list'] ?? [] as $permission)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="permission[]" value="{{ $permission['key'] }}"
                                        class="border-input size-4 rounded" @checked(in_array($permission['key'], $granted, true))>
                                    {{ $permission['title'] }}
                                </label>
                            @endforeach
                        </x-ui.card-content>
                    </x-ui.card>
                @endforeach
            </div>
        </div>
    @endisset

    <div class="flex gap-2">
        <x-ui.button type="submit">Submit</x-ui.button>
        <x-ui.button :href="route($back)" variant="outline" class="pjax">Back</x-ui.button>
    </div>
</form>

@push('scripts')
    <script>
        documentReady(function() {
            $('#ajax-form').validate({
                rules: {
                    first_name: {required: true, minlength: 2},
                    last_name: {required: true, minlength: 2},
                    email: {required: true, email: true},
                    password: {required: {{ isset($model) ? 'false' : 'true' }}, minlength: 6},
                },
                submitHandler: function(form) {
                    app.ajaxFileForm(form);
                },
            });
        });
    </script>
@endpush

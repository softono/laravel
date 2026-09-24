@extends($layout)
@section('title')
    Profile
@endsection
@section('content')

    <div>
        @include('modules.user.account.component.account_block')
        <div class="space-y-4">
            <x-ui.card>
                <x-ui.card-content>

                    <div class="flex items-start gap-6 border-b border-border pb-4 sm:items-center">
                        <img src="{{ $general->getFileUrl($model->image, 'profile') }}" alt="user-avatar"
                            class="block h-[100px] w-[100px] rounded" height="100px" width="100px"
                            id="uploadedAvatar">
                        <div>
                            <x-ui.button onclick="app.showModalView('{{ route($prefix.'account/image') }}')" class="mb-4 mr-3">
                                <span class="hidden sm:block">Upload new photo</span>
                                <i class="bx bx-upload block sm:hidden"></i>
                            </x-ui.button>
                            <div class="text-sm text-muted-foreground">Allowed JPG, GIF or PNG.</div>
                        </div>
                    </div>
                </x-ui.card-content>
                <x-ui.card-content class="pt-4">
                    <form action="{{ route($prefix.'account/update-process') }}" method="post" id="ajax-form">
                        {{ csrf_field() }}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <x-ui.label for="firstName" class="mb-2">First Name <span
                                        class="text-destructive">*</span></x-ui.label>
                                <x-ui.input type="text" id="first_name" name="first_name"
                                    value="{{ $model->first_name }}" autofocus="" placeholder="Enter First Name"
                                    required="required" maxlength="128" />
                            </div>

                            <div>
                                <x-ui.label for="lastName" class="mb-2">Last Name <span
                                        class="text-destructive">*</span></x-ui.label>
                                <x-ui.input type="text" value="{{ $model->last_name }}" name="last_name"
                                    id="last_name" placeholder="Enter Last Name" required="required" maxlength="128" />
                            </div>

                            <div>
                                <x-ui.label for="email" class="mb-2">E-mail</x-ui.label>
                                <x-ui.input type="email" value="{{ $model->email }}" id="email"
                                    readonly disabled />
                            </div>

                            <div>
                                <x-ui.label class="mb-2" for="phone">Phone Number</x-ui.label>
                                <x-ui.input type="tel" value="{{ $model->phone }}" name="phone"
                                    id="phone" placeholder="Enter Phone" />
                            </div>

                            <div>
                                <x-ui.label class="mb-2" for="country">Country</x-ui.label>
                                <x-ui.select name="country" id="country">
                                    <option value="">Select country</option>
                                    @foreach ($countries as $code => $name)
                                        <option value="{{ $code }}" @selected($model->country === $code)>{{ $name }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>

                            <div>
                                <x-ui.label class="mb-2" for="timezone">Timezone</x-ui.label>
                                <x-ui.select name="timezone" id="timezone">
                                    @foreach ($timezones as $timezone)
                                        <option value="{{ $timezone }}" @selected($model->timezone === $timezone)>{{ $timezone }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                        </div>
                        <div class="mt-6">
                            <x-ui.button type="submit" class="mr-3">Save changes</x-ui.button>
                            <x-ui.button variant="secondary" type="reset">Cancel</x-ui.button>
                        </div>
                    </form>
                </x-ui.card-content>
            </x-ui.card>
            @include('modules.user.account.component.email_change')
            <x-ui.card>
                <x-ui.card-header>
                    <x-ui.card-title>Deactivate Account</x-ui.card-title>
                </x-ui.card-header>
                <x-ui.card-content>
                    <div class="mb-6">
                        <x-ui.alert variant="warning">
                            <x-ui.alert-title>Are you sure you want to deactivate your account?</x-ui.alert-title>
                            <x-ui.alert-description>Your account will be deactivated and you will be signed out on every device.</x-ui.alert-description>
                        </x-ui.alert>
                    </div>
                    <form action="{{ route($prefix.'account/deactivate') }}" id="formAccountDeactivation" method="POST">
                        @csrf
                        <div class="my-8 ml-2 flex items-center gap-2">
                            <x-ui.checkbox name="accountActivation" id="accountActivation" required />
                            <x-ui.label for="accountActivation" class="font-normal">I confirm my account
                                deactivation</x-ui.label>
                        </div>
                        <x-ui.button variant="destructive" type="submit" class="deactivate-account">Deactivate Account</x-ui.button>
                    </form>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/account/email-change.js') }}"></script>
    <script type="text/javascript">
        documentReady(function() {
            emailChange.init({
                startUrl: @json(route($prefix.'account/email/start')),
                resendUrl: @json(route($prefix.'account/email/resend')),
                verifyNewUrl: @json(route($prefix.'account/email/verify-new')),
                sendOtpUrl: @json(route($prefix.'account/email/send-otp')),
                verifyUrl: @json(route($prefix.'account/email/verify')),
            });
            $('#ajax-form').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            })
        });
    </script>
@endpush

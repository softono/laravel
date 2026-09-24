@extends($layout)
@section('title')
    Change password
@endsection
@section('content')
    <div>
        @include('modules.user.account.component.account_block')
        @unless ($hasPassword)
            <x-ui.card>
                <x-ui.card-header>
                    <x-ui.card-title>Set a Password</x-ui.card-title>
                </x-ui.card-header>
                <x-ui.card-content>
                    <p class="text-sm text-muted-foreground">You signed up with Google and have no password yet. Set one to also sign in with your email.</p>
                </x-ui.card-content>
                <x-ui.card-content>
                    <form action="{{ url('auth/set-password') }}" method="post" id="set-password-form" class="grid max-w-md gap-4"
                        data-next="reload">
                        @csrf
                        <div class="grid gap-2">
                            <x-ui.label for="set-password">New password</x-ui.label>
                            <x-ui.input type="password" id="set-password" name="password" minlength="6" maxlength="100" autocomplete="new-password" required />
                        </div>
                        <div class="grid gap-2">
                            <x-ui.label for="set-password-confirm">Confirm new password</x-ui.label>
                            <x-ui.input type="password" id="set-password-confirm" name="confirm_password" minlength="6" maxlength="100" autocomplete="new-password" required />
                        </div>
                        <div><x-ui.button type="submit">Set password</x-ui.button></div>
                    </form>
                </x-ui.card-content>
            </x-ui.card>
        @else
        <x-ui.card>
            <x-ui.card-header>
                <x-ui.card-title>Change Password</x-ui.card-title>
            </x-ui.card-header>
            <x-ui.card-content>
                <p class="mb-4 text-sm text-muted-foreground">Changing your password signs you out of every device, including this one.</p>
                <form action="{{ url('auth/change-password') }}" method="post" id="ajax-form"
                    data-next="redirect" data-next-url="{{ route($loginRoute) }}">
                    {{ csrf_field() }}
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <x-ui.label class="mb-2" for="currentPassword">Current Password <span
                                    class="text-destructive">*</span></x-ui.label>
                            <x-ui.password-input maxlength="32" minlength="6" name="current_password" id="current_password" required="required" />
                            <label id="current_password-error" class="error" for="current_password"
                                style="display:none;"></label>
                        </div>
                    </div>
                    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <x-ui.label class="mb-2" for="newPassword">New Password <span
                                    class="text-destructive">*</span></x-ui.label>
                            <x-ui.password-input maxlength="32" minlength="6" id="password" name="password" required="required" />
                            <label id="password-error" class="error" for="password" style="display:none;"></label>
                        </div>
                        <div>
                            <x-ui.label class="mb-2" for="confirmPassword">Confirm New Password <span
                                    class="text-destructive">*</span></x-ui.label>
                            <x-ui.password-input maxlength="32" minlength="6" name="confirm_password" id="confirm_password" required="required" />
                            <label id="confirm_password-error" class="error" for="confirm_password"
                                style="display:none;"></label>
                        </div>
                        <div class="md:col-span-2">
                            <h6 class="text-foreground">Password Requirements:</h6>
                            <ul class="mb-0 list-disc pl-8">
                                <li class="mb-4">Password must be at least 6 characters long.</li>
                            </ul>
                        </div>
                        <div class="mt-2 md:col-span-2">
                            <x-ui.button type="submit" class="mr-2">Save changes</x-ui.button>
                            <x-ui.button variant="secondary" type="reset">Cancel</x-ui.button>
                        </div>
                    </div>
                </form>
            </x-ui.card-content>
        </x-ui.card>
        @endunless
    </div>

    <script type="text/javascript">
        documentReady(function() {
            $('#set-password-form').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            });
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
            })
        });
    </script>
@endsection

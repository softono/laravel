{{-- "Change email" card: password + new address, then a code mailed to the new address, then a second proof of ownership. --}}
<x-ui.card id="email-change" class="mt-4">
    <x-ui.card-header>
        <x-ui.card-title>Email Address</x-ui.card-title>
    </x-ui.card-header>
    <x-ui.card-content>
        <p class="text-sm text-muted-foreground">Currently <span class="font-medium text-foreground">{{ $model->email }}</span>. Changing it requires your password and a code sent to the new address.</p>
    </x-ui.card-content>
    <x-ui.card-content>
        <form data-step="start" class="grid max-w-md gap-4">
            <div class="grid gap-2">
                <x-ui.label for="email-change-email">New email</x-ui.label>
                <x-ui.input type="email" id="email-change-email" name="email" placeholder="name@example.com" required />
            </div>
            <div class="grid gap-2">
                <x-ui.label for="email-change-password">Current password</x-ui.label>
                <x-ui.input type="password" id="email-change-password" name="password" autocomplete="current-password" required />
            </div>
            <div><x-ui.button type="submit">Send verification code</x-ui.button></div>
        </form>

        <form data-step="verify-new" class="hidden max-w-md gap-4">
            <p class="text-sm text-muted-foreground">Enter the 6-digit code we sent to <span class="font-medium text-foreground" data-new-email></span>.</p>
            <div class="grid gap-2">
                <x-ui.label for="email-change-new-code">Verification code</x-ui.label>
                <x-ui.input id="email-change-new-code" name="code" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required />
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.button type="submit">Verify</x-ui.button>
                <x-ui.button type="button" variant="outline" data-action="resend">Resend code</x-ui.button>
                <x-ui.button type="button" variant="ghost" data-action="cancel">Cancel</x-ui.button>
            </div>
        </form>

        <form data-step="verify" class="hidden max-w-md gap-4">
            <p class="text-sm text-muted-foreground">One more step: confirm it is you.</p>
            <div class="grid gap-2">
                <x-ui.label for="email-change-method">Verify with</x-ui.label>
                <x-ui.select id="email-change-method" name="method"></x-ui.select>
            </div>
            <div class="grid gap-2">
                <x-ui.label for="email-change-code" data-code-label>Code</x-ui.label>
                <x-ui.input id="email-change-code" name="code" autocomplete="one-time-code" required />
            </div>
            <div class="flex flex-wrap gap-2">
                <x-ui.button type="submit">Change email</x-ui.button>
                <x-ui.button type="button" variant="outline" data-action="send-otp" class="hidden">Send code to current email</x-ui.button>
                <x-ui.button type="button" variant="ghost" data-action="cancel">Cancel</x-ui.button>
            </div>
        </form>
    </x-ui.card-content>
</x-ui.card>

{{-- Password field with a show/hide toggle (behaviour: app.ui in assets/js/app.js). Attributes go to the input; give it an id. --}}
<div data-slot="password-input" class="relative">
    <x-ui.input type="password" {{ $attributes->class(['pr-10']) }} />
    <button type="button" data-password-toggle="#{{ $attributes->get('id') }}" tabindex="-1" aria-label="Show or hide password"
        class="text-muted-foreground hover:text-foreground absolute inset-y-0 right-0 flex w-10 cursor-pointer items-center justify-center">
        <i class="bx bx-hide"></i>
    </button>
</div>

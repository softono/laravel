{{--
    Light / dark / system picker. Markup only: assets/js/app.js (app.ui.theme) stores the choice in localStorage
    ("app-color-mode", the key Next uses), toggles `dark` and data-theme on <html>, marks the active option with
    aria-checked and swaps the trigger icon. The class strings below stay in this file so Tailwind generates them.
--}}
@props(['float' => false])
@php
    $options = ['light' => ['bx-sun', 'Light'], 'dark' => ['bx-moon', 'Dark'], 'system' => ['bx-desktop', 'System']];
@endphp
<div data-slot="theme-switch" data-dropdown {{ $attributes->class(['relative', 'fixed top-4 right-4 z-40' => $float]) }}>
    <button type="button" data-dropdown-toggle aria-haspopup="menu" aria-label="Change theme" title="Theme"
        class="border-border bg-card text-foreground hover:bg-muted focus-visible:ring-ring/50 flex size-9 cursor-pointer items-center justify-center rounded-lg border outline-none transition-colors duration-150 focus-visible:ring-[3px]">
        <i class="bx bx-sun text-lg" data-theme-icon></i>
    </button>
    <div data-dropdown-menu role="menu" aria-label="Theme"
        class="bg-popover text-popover-foreground border-border absolute right-0 z-50 mt-2 hidden w-40 rounded-xl border p-1 shadow-lg">
        @foreach ($options as $mode => [$icon, $label])
            <button type="button" role="menuitemradio" aria-checked="false" data-theme-option="{{ $mode }}"
                class="group text-muted-foreground hover:bg-muted hover:text-foreground aria-checked:bg-accent aria-checked:text-accent-foreground flex w-full cursor-pointer items-center gap-2.5 rounded-md px-2 py-2 text-sm transition-colors aria-checked:font-semibold">
                <i class="bx {{ $icon }} text-base"></i>
                <span class="flex-1 text-left">{{ $label }}</span>
                <i class="bx bx-check text-base opacity-0 group-aria-checked:opacity-100"></i>
            </button>
        @endforeach
    </div>
</div>

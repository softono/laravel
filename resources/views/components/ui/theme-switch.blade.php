{{--
    Colour theme + light/dark/system picker: Next's "switchcn" ThemeSwitcher. Markup only; app.ui.theme in
    assets/js/app.js does the work. The theme rows are cloned from the <template> below the first time the
    popover opens (list from GET /theme); picking one loads its CSS from GET /theme/{name}. Class strings live in
    this file so Tailwind generates them.
--}}
@props(['float' => false])
@php
    $active = \App\Helpers\ThemeCatalog::activeName(request());
    $label = collect(\App\Helpers\ThemeCatalog::all())->firstWhere('name', $active)['label'] ?? 'Default';
    $modeIcons = [
        'light' => '<circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.8"/><path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2 12h2M20 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
        'system' => '<rect x="2" y="3" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M8 21h8M12 17v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
        'dark' => '<path d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>',
    ];
@endphp
<div data-slot="theme-switch" data-theme-switch data-dropdown
    data-themes-url="{{ route('theme') }}" data-theme-url="{{ route('theme/show', ['name' => '__name__']) }}"
    {{ $attributes->class(['relative', 'fixed top-4 right-4 z-40' => $float]) }}>
    <button type="button" data-dropdown-toggle data-theme-trigger aria-haspopup="dialog" aria-label="Change theme"
        title="Active Theme: {{ $label }}"
        class="border-border bg-card text-foreground hover:bg-muted focus-visible:ring-ring/50 flex size-9 cursor-pointer items-center justify-center rounded-lg border outline-none transition-colors duration-150 focus-visible:ring-[3px]">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="opacity-90">
            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 22 2 12 2C6.47715 2 2 6.47715 2 12C2 14.7255 3.09032 17.1962 4.85857 19C5.34458 19.486 5.38575 20.2528 4.95759 20.7879C4.54284 21.3063 3.8647 22 3 22" />
            <circle cx="7.5" cy="10.5" r="1.5" fill="currentColor" />
            <circle cx="11.5" cy="7.5" r="1.5" fill="currentColor" />
            <circle cx="16.5" cy="9.5" r="1.5" fill="currentColor" />
            <circle cx="15.5" cy="14.5" r="1.5" fill="currentColor" />
        </svg>
    </button>

    <div data-dropdown-menu role="dialog" aria-label="Theme"
        class="bg-popover text-popover-foreground border-border absolute right-0 z-[100] mt-1.5 hidden w-[280px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border p-0 shadow-lg">
        <div class="px-3 pt-3">
            <div class="bg-input/30 border-border flex items-center gap-2 rounded-lg border px-3 py-1.5">
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" class="text-muted-foreground shrink-0">
                    <circle cx="7" cy="7" r="5" stroke="currentColor" stroke-width="1.6" />
                    <path d="M11 11l3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
                <input type="text" data-theme-search placeholder="Search themes…" autocomplete="off" aria-label="Search themes"
                    class="text-foreground caret-foreground flex-1 border-none bg-transparent text-sm outline-none" />
            </div>
        </div>

        <div class="flex items-center justify-between px-3.5 pt-2 pb-1">
            <span class="text-muted-foreground text-xs" data-theme-count></span>
            <div class="flex gap-1">
                <button type="button" data-keep-open data-theme-cycle title="Mode: system — click to cycle" aria-label="Cycle colour mode"
                    class="text-muted-foreground hover:bg-muted flex cursor-pointer rounded-md border-none bg-transparent p-1">
                    @foreach ($modeIcons as $mode => $paths)
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" data-theme-mode-icon="{{ $mode }}" class="hidden">{!! $paths !!}</svg>
                    @endforeach
                </button>
                <button type="button" data-keep-open data-theme-shuffle title="Random theme" aria-label="Random theme"
                    class="text-muted-foreground hover:bg-muted flex cursor-pointer rounded-md border-none bg-transparent p-1">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none">
                        <path d="M16 3h5v5M4 20L21 3M21 16v5h-5M15 15l6 6M4 4l5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="border-border border-b px-3.5 py-1">
            <span class="text-muted-foreground text-[10px] font-bold tracking-wide uppercase">Built-in Themes</span>
        </div>

        <div data-theme-list class="max-h-[min(20rem,calc(100dvh-13rem))] overflow-y-auto p-1 pb-1.5 [scrollbar-color:var(--color-muted)_transparent] [scrollbar-width:thin]">
            <div data-theme-empty class="text-muted-foreground hidden py-4 text-center text-sm">No themes found</div>
        </div>

        <template data-theme-row-template>
            <button type="button" data-theme-name
                class="text-foreground hover:bg-muted aria-checked:bg-accent aria-checked:hover:bg-accent group flex w-full cursor-pointer items-center gap-2.5 rounded-md border-none bg-transparent px-2 py-2 transition-colors duration-100"
                role="menuitemradio" aria-checked="false">
                <span class="flex min-w-[60px] items-center gap-1" data-theme-swatches></span>
                <span data-theme-label class="text-muted-foreground group-aria-checked:text-foreground flex-1 text-left text-sm font-normal group-aria-checked:font-semibold"></span>
                <svg width="14" height="14" viewBox="0 0 16 16" fill="none" class="text-muted-foreground opacity-0 group-aria-checked:opacity-100">
                    <path d="M3 8l4 4 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </template>
    </div>
</div>

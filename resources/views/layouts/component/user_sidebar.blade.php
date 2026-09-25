{{--
    Next's AppSidebar (src/app/(user)/layout/app-sidebar.tsx + nav-user.tsx): inset variant that collapses to an icon rail on
    md+ and becomes an off-canvas sheet below md. State lives in `data-state` on #user-shell (expanded | collapsed); app.ui.appSidebar
    in assets/js/app.js toggles it and remembers it in the `sidebar_state` cookie. Class strings stay in this file for Tailwind.
--}}
@php
    $navItems = [
        ['title' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'bx-grid-alt'],
        ['title' => 'Notes', 'route' => 'notes', 'icon' => 'bx-note'],
    ];
    $displayName = trim(($sessionUser->first_name ?? '').' '.($sessionUser->last_name ?? '')) ?: 'User';
    $initials = strtoupper(collect(explode(' ', $displayName))->map(fn ($part) => mb_substr($part, 0, 1))->implode(''));
    $menuButton = 'flex w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm outline-none ring-sidebar-ring transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2 active:bg-sidebar-accent md:group-data-[state=collapsed]/shell:size-8! md:group-data-[state=collapsed]/shell:p-2! [&>span:last-child]:truncate';
    $menuButtonLg = 'flex h-12 w-full items-center gap-2 overflow-hidden rounded-md p-2 text-left text-sm outline-none ring-sidebar-ring transition-[width,height,padding] hover:bg-sidebar-accent hover:text-sidebar-accent-foreground focus-visible:ring-2 active:bg-sidebar-accent md:group-data-[state=collapsed]/shell:size-8! md:group-data-[state=collapsed]/shell:p-0!';
    $dropdownItem = 'flex w-full cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-accent hover:text-accent-foreground [&_i]:text-base [&_i]:text-muted-foreground';
@endphp
<div data-app-sidebar-backdrop class="fixed inset-0 z-40 hidden bg-black/50 md:hidden"></div>

{{-- Takes the sidebar's width in the flex row so the inset does not slide under the fixed panel. --}}
<div data-slot="sidebar-gap"
    class="relative hidden w-(--sidebar-width) shrink-0 bg-transparent transition-[width] duration-200 ease-linear group-data-[state=collapsed]/shell:w-[calc(var(--sidebar-width-icon)+1rem)] md:block"></div>

<aside id="app-sidebar" data-slot="sidebar-container" aria-label="Sidebar"
    class="fixed inset-y-0 left-0 z-50 flex h-svh w-72 -translate-x-full transition-[transform,width] duration-200 ease-linear max-md:border-sidebar-border max-md:border-r md:w-(--sidebar-width) md:translate-x-0 md:p-2 md:group-data-[state=collapsed]/shell:w-[calc(var(--sidebar-width-icon)+1rem+2px)]">
    <div class="bg-sidebar text-sidebar-foreground relative flex h-full w-full flex-col">
        {{-- Header: logo and app name --}}
        <div class="flex flex-col gap-2 p-2">
            <a href="{{ route('dashboard') }}" class="pjax {{ $menuButtonLg }}" title="{{ config('setting.app_name') }}">
                <div class="flex aspect-square size-8 shrink-0 items-center justify-center rounded-lg">
                    <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}" alt="{{ config('setting.app_name') }}" width="32" height="32" class="h-full w-full rounded-lg object-contain" />
                </div>
                <div class="grid flex-1 text-left text-sm leading-tight">
                    <span class="truncate font-semibold">{{ config('setting.app_name') }}</span>
                </div>
            </a>
        </div>

        {{-- Content: navigation --}}
        <div class="flex min-h-0 flex-1 flex-col gap-2 overflow-auto md:group-data-[state=collapsed]/shell:overflow-hidden">
            <div class="relative flex w-full min-w-0 flex-col p-2">
                <ul class="flex w-full min-w-0 flex-col gap-1">
                    @foreach ($navItems as $item)
                        <li data-active_menu_links="{{ $item['route'] }}"
                            class="active-menu group/menu-item relative [&.active>a]:bg-sidebar-accent [&.active>a]:font-medium [&.active>a]:text-sidebar-accent-foreground {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                            <a href="{{ route($item['route']) }}" class="pjax {{ $menuButton }} h-8" title="{{ $item['title'] }}">
                                <i class="bx {{ $item['icon'] }} shrink-0 text-base"></i>
                                <span>{{ $item['title'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Footer: the signed-in user --}}
        <div class="flex flex-col gap-2 p-2">
            <div class="relative" data-dropdown>
                <button type="button" data-dropdown-toggle aria-haspopup="menu" class="{{ $menuButtonLg }}" title="{{ $displayName }}">
                    <span class="bg-muted text-foreground flex size-8 shrink-0 items-center justify-center overflow-hidden rounded-lg text-xs font-medium">
                        @if ($sessionUser->image)
                            <img src="{{ $general->getFileUrl($sessionUser->image, 'profile') }}" alt="{{ $displayName }}" class="size-full object-cover" />
                        @else
                            {{ $initials }}
                        @endif
                    </span>
                    <span class="grid flex-1 text-left text-sm leading-tight">
                        <span class="truncate font-semibold">{{ $displayName }}</span>
                        <span class="truncate text-xs">{{ $sessionUser->email }}</span>
                    </span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ms-auto size-4 shrink-0"><path d="m7 15 5 5 5-5" /><path d="m7 9 5-5 5 5" /></svg>
                </button>
                <div data-dropdown-menu role="menu"
                    class="bg-popover text-popover-foreground border-border absolute bottom-full left-0 z-50 mb-1 hidden min-w-56 rounded-lg border p-1 shadow-md max-md:right-0 md:bottom-0 md:left-full md:mb-0 md:ml-4 md:w-56">
                    <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                        <span class="bg-muted text-foreground flex size-8 shrink-0 items-center justify-center overflow-hidden rounded-lg text-xs font-medium">
                            @if ($sessionUser->image)
                                <img src="{{ $general->getFileUrl($sessionUser->image, 'profile') }}" alt="{{ $displayName }}" class="size-full object-cover" />
                            @else
                                {{ $initials }}
                            @endif
                        </span>
                        <span class="grid flex-1 text-left text-sm leading-tight">
                            <span class="truncate font-semibold">{{ $displayName }}</span>
                            <span class="truncate text-xs">{{ $sessionUser->email }}</span>
                        </span>
                    </div>
                    <div class="bg-border -mx-1 my-1 h-px"></div>
                    <a href="{{ route('account/update') }}" class="pjax {{ $dropdownItem }}" role="menuitem"><i class="bx bx-badge-check"></i> My Account</a>
                    <div class="bg-border -mx-1 my-1 h-px"></div>
                    <button type="button" role="menuitem" class="{{ $dropdownItem }} text-destructive hover:bg-destructive/10 hover:text-destructive [&_i]:text-destructive"
                        data-confirm-href="{{ route('logout') }}" data-confirm-title="Are you sure you want to sign out?"
                        data-confirm-text="This action will log you out of your account. You will need to sign in again to access your account."
                        data-confirm-yes="Sign out">
                        <i class="bx bx-log-out"></i> Sign out
                    </button>
                </div>
            </div>
        </div>

        {{-- Rail: click the sidebar's edge to collapse or expand it --}}
        <button type="button" data-app-sidebar-toggle tabindex="-1" aria-label="Toggle Sidebar" title="Toggle Sidebar"
            class="hover:after:bg-sidebar-border absolute inset-y-0 -right-3 z-20 hidden w-4 cursor-w-resize transition-all ease-linear group-data-[state=collapsed]/shell:cursor-e-resize after:absolute after:inset-y-0 after:left-1/2 after:w-[2px] md:flex"></button>
    </div>
</aside>

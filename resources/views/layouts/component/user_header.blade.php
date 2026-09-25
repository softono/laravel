{{-- Next's AuthenticatedLayout header: sidebar trigger, separator, then theme switch and the profile dropdown. --}}
@php
    $displayName = trim(($sessionUser->first_name ?? '').' '.($sessionUser->last_name ?? '')) ?: 'User';
    $initials = strtoupper(collect(explode(' ', $displayName))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode(''));
    $dropdownItem = 'flex w-full cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-none hover:bg-accent hover:text-accent-foreground [&_i]:text-base [&_i]:text-muted-foreground';
@endphp
<header class="z-30 h-16 shrink-0">
    <div class="flex h-full items-center gap-3 p-4 sm:gap-4">
        <button type="button" data-app-sidebar-toggle aria-label="Toggle Sidebar" aria-controls="app-sidebar" aria-expanded="true"
            class="border-border bg-background hover:bg-accent hover:text-accent-foreground dark:bg-input/30 dark:hover:bg-input/50 inline-flex size-7 shrink-0 cursor-pointer items-center justify-center rounded-md border shadow-xs outline-none transition-all focus-visible:ring-[3px] focus-visible:ring-ring/50 max-md:scale-125">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" /><path d="M9 3v18" /></svg>
        </button>
        <div class="bg-border h-6 w-px shrink-0"></div>

        <div class="ms-auto flex items-center space-x-4">
            <x-ui.theme-switch />

            <div class="relative" data-dropdown>
                <button type="button" data-dropdown-toggle aria-haspopup="menu" aria-label="Account menu"
                    class="hover:bg-accent focus-visible:ring-ring/50 relative flex size-10 cursor-pointer items-center justify-center rounded-full outline-none focus-visible:ring-[3px]">
                    <span class="bg-muted text-foreground flex size-full items-center justify-center overflow-hidden rounded-full text-sm font-medium">
                        @if ($sessionUser->image)
                            <img src="{{ $general->getFileUrl($sessionUser->image, 'profile') }}" alt="{{ $displayName }}" class="size-full object-cover" />
                        @else
                            {{ $initials }}
                        @endif
                    </span>
                    <span class="bg-success border-background absolute right-0.5 bottom-0.5 size-2.5 rounded-full border-2"></span>
                </button>
                <div data-dropdown-menu role="menu"
                    class="bg-popover text-popover-foreground border-border absolute right-0 top-full z-50 mt-1 hidden w-56 rounded-md border p-1 shadow-md">
                    <a href="{{ route('account/update') }}" class="pjax flex items-center gap-3 rounded-sm px-2 py-1.5 hover:bg-accent">
                        <span class="bg-muted flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-full text-sm font-medium">
                            @if ($sessionUser->image)
                                <img src="{{ $general->getFileUrl($sessionUser->image, 'profile') }}" alt="{{ $displayName }}" class="size-full object-cover" />
                            @else
                                {{ $initials }}
                            @endif
                        </span>
                        <span class="flex min-w-0 flex-col gap-1">
                            <span class="truncate text-sm leading-none font-medium">{{ $displayName }}</span>
                            <span class="text-muted-foreground truncate text-xs">{{ $sessionUser->email }}</span>
                        </span>
                    </a>
                    <div class="bg-border -mx-1 my-1 h-px"></div>
                    <a href="{{ route('account/update') }}" class="pjax {{ $dropdownItem }}" role="menuitem"><i class="bx bx-user"></i> My Account</a>
                    <div class="bg-border -mx-1 my-1 h-px"></div>
                    <button type="button" role="menuitem" class="{{ $dropdownItem }} text-destructive hover:bg-destructive/10 hover:text-destructive [&_i]:text-destructive"
                        data-confirm-href="{{ route('logout') }}" data-confirm-title="Log Out"
                        data-confirm-text="Are you sure you want to Log out? You will need to login again to access your account."
                        data-confirm-yes="Log Out">
                        <i class="bx bx-power-off"></i> Log Out
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<nav class="flex items-center justify-between gap-4 border-b border-border bg-card px-4 py-3 sm:px-6 lg:px-8" id="layout-navbar">
    <button type="button" class="text-muted-foreground hover:text-foreground lg:hidden" data-sidebar-toggle="open" aria-label="Open sidebar">
        <i class="bx bx-menu text-xl"></i>
    </button>

    <div class="flex flex-1 items-center justify-end gap-3">
        <x-ui.theme-switch />

        <!-- User -->
        <div class="relative" data-dropdown>
            <button type="button" class="flex items-center" data-dropdown-toggle>
                <img src="{{ $general->getFileUrl($sessionUser->image, 'profile') }}" alt class="h-9 w-9 rounded-full object-cover" />
            </button>
            <ul data-dropdown-menu class="hidden absolute right-0 z-20 mt-2 w-56 rounded-md border border-border bg-card py-1 shadow-lg">
                <li>
                    <a class="pjax flex items-center gap-3 px-4 py-2 hover:bg-accent" href="{{ route('admin/account/update') }}">
                        <img src="{{ $general->getFileUrl($sessionUser->image, 'profile') }}" alt class="h-9 w-9 rounded-full object-cover" />
                        <span>
                            <span class="block text-sm font-semibold text-foreground">{{ $sessionUser->first_name . ' ' . $sessionUser->last_name }}</span>
                            <small class="text-muted-foreground">{{ $sessionUser->email }}</small>
                        </span>
                    </a>
                </li>
                <li class="my-1 border-t border-border"></li>
                <li>
                    <a class="pjax flex items-center gap-3 px-4 py-2 text-sm text-muted-foreground hover:bg-accent" href="{{ route('admin/account/update') }}">
                        <i class="bx bx-user"></i> My Account
                    </a>
                </li>
                <li>
                    <a class="pjax flex items-center gap-3 px-4 py-2 text-sm text-muted-foreground hover:bg-accent" href="{{ route('admin/setting/update') }}">
                        <i class="bx bx-cog"></i> Settings
                    </a>
                </li>
                <li class="my-1 border-t border-border"></li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2 text-sm text-muted-foreground hover:bg-accent" href="{{ route('admin/auth/logout') }}">
                        <i class="bx bx-power-off"></i> Log Out
                    </a>
                </li>
            </ul>
        </div>
        <!--/ User -->
    </div>
</nav>

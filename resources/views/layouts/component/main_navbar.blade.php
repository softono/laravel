<nav class="border-b border-border bg-card">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="pjax flex items-center gap-2">
            <img src="{{ $general->getFileUrl(config('setting.app_logo'),'logo')}}" alt="{{ config('setting.app_name') }}" class="h-8 w-8 rounded" />
            <span class="text-lg font-bold text-foreground">{{ config('setting.app_name') }}</span>
        </a>

        <div class="flex items-center gap-2 lg:hidden">
            <x-ui.theme-switch />
            <x-ui.button variant="outline" size="icon" class="rounded-lg" data-collapse-toggle="#mobile-menu" aria-label="Toggle navigation">
                <i class="bx bx-menu text-xl" data-toggle-icon></i>
                <i class="bx bx-x hidden text-xl" data-toggle-icon></i>
            </x-ui.button>
        </div>

        <div class="hidden items-center gap-6 lg:flex">
            <a class="pjax text-sm font-medium text-muted-foreground hover:text-foreground" href="{{ route('home') }}">Home</a>
            <a class="pjax text-sm font-medium text-muted-foreground hover:text-foreground" href="{{ route('blog') }}">Blog</a>
            <a class="pjax text-sm font-medium text-muted-foreground hover:text-foreground" href="{{ route('contact') }}">Contact</a>
            <?php if ($sessionUser) { ?>
            <a class="pjax text-sm font-medium text-muted-foreground hover:text-foreground" href="{{ route('notes') }}">Notes</a>
            <?php } ?>
        </div>

        <div class="hidden items-center gap-3 lg:flex">
            <x-ui.theme-switch />
            <?php if ($sessionUser) { ?>
            <div class="relative" data-dropdown>
                <button type="button" class="flex items-center gap-2" data-dropdown-toggle>
                    <img src="{{ $general->getFileUrl($sessionUser->image,'profile') }}" alt class="h-9 w-9 rounded-full object-cover" />
                </button>
                <div data-dropdown-menu class="hidden absolute right-0 z-20 mt-2 w-56 rounded-md border border-border bg-card py-1 shadow-lg">
                    <a class="pjax flex items-center gap-3 px-4 py-2 hover:bg-accent" href="{{ route('account/update') }}">
                        <img src="{{ $general->getFileUrl($sessionUser->image,'profile') }}" alt class="h-9 w-9 rounded-full object-cover" />
                        <span>
                            <span class="block text-sm font-semibold text-foreground">{{$sessionUser->first_name.' '.$sessionUser->last_name}}</span>
                            <small class="text-muted-foreground">{{ $sessionUser->email }}</small>
                        </span>
                    </a>
                    <div class="my-1 border-t border-border"></div>
                    <a class="pjax flex items-center gap-3 px-4 py-2 text-sm text-muted-foreground hover:bg-accent" href="{{ route('account/update') }}">
                        <i class="bx bx-user"></i> My Account
                    </a>
                    <div class="my-1 border-t border-border"></div>
                    <a class="flex items-center gap-3 px-4 py-2 text-sm text-muted-foreground hover:bg-accent" href="{{ route('logout') }}">
                        <i class="bx bx-power-off"></i> Log Out
                    </a>
                </div>
            </div>
            <?php } else { ?>
            <x-ui.button href="login" class="rounded-full pjax" data-pjax-layout="blank">
                Login <i class="bx bx-log-in-circle"></i>
            </x-ui.button>
            <x-ui.button href="register" class="rounded-full pjax" data-pjax-layout="blank">
                Register <i class="bx bx-user"></i>
            </x-ui.button>
            <?php } ?>
        </div>
    </div>

    <div id="mobile-menu" class="hidden space-y-1 border-t border-border px-4 py-3 lg:hidden">
        <a class="pjax block rounded-md px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-accent" href="{{ route('home') }}">Home</a>
        <a class="pjax block rounded-md px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-accent" href="{{ route('blog') }}">Blog</a>
        <a class="pjax block rounded-md px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-accent" href="{{ route('contact') }}">Contact</a>
        <?php if ($sessionUser) { ?>
        <a class="pjax block rounded-md px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-accent" href="{{ route('notes') }}">Notes</a>
        <?php } ?>
        <?php if ($sessionUser) { ?>
        <a class="pjax block rounded-md px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-accent" href="{{ route('account/update') }}">My Account</a>
        <a class="block rounded-md px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-accent" href="{{ route('logout') }}">Log Out</a>
        <?php } else { ?>
        <a href="login" class="block rounded-md px-3 py-2 text-sm font-medium text-primary hover:bg-accent pjax" data-pjax-layout="blank">Login</a>
        <a href="register" class="block rounded-md px-3 py-2 text-sm font-medium text-primary hover:bg-accent pjax" data-pjax-layout="blank">Register</a>
        <?php } ?>
    </div>
</nav>

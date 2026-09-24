<nav class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="pjax flex items-center gap-2">
            <img src="{{ $general->getFileUrl(config('setting.app_logo'),'logo')}}" alt="{{ config('setting.app_name') }}" class="h-8 w-8 rounded" />
            <span class="text-lg font-bold text-slate-800">{{ config('setting.app_name') }}</span>
        </a>

        <button type="button" class="btn-icon border border-slate-200 lg:hidden" data-collapse-toggle="#mobile-menu" aria-label="Toggle navigation">
            <i class="bx bx-menu text-xl" data-toggle-icon></i>
            <i class="bx bx-x hidden text-xl" data-toggle-icon></i>
        </button>

        <div class="hidden items-center gap-6 lg:flex">
            <a class="pjax text-sm font-medium text-slate-600 hover:text-primary-600" href="{{ route('home') }}">Home</a>
            <a class="pjax text-sm font-medium text-slate-600 hover:text-primary-600" href="{{ route('blog') }}">Blog</a>
            <a class="pjax text-sm font-medium text-slate-600 hover:text-primary-600" href="{{ route('contact') }}">Contact</a>
            <?php if ($sessionUser) { ?>
            <a class="pjax text-sm font-medium text-slate-600 hover:text-primary-600" href="{{ route('notes') }}">Notes</a>
            <?php } ?>
        </div>

        <div class="hidden items-center gap-3 lg:flex">
            <?php if ($sessionUser) { ?>
            <div class="relative" data-dropdown>
                <button type="button" class="flex items-center gap-2" data-dropdown-toggle>
                    <img src="{{ $general->getFileUrl($sessionUser->image,'profile') }}" alt class="h-9 w-9 rounded-full object-cover" />
                </button>
                <div data-dropdown-menu class="hidden absolute end-0 z-20 mt-2 w-56 rounded-md border border-slate-200 bg-white py-1 shadow-lg">
                    <a class="pjax flex items-center gap-3 px-4 py-2 hover:bg-slate-50" href="{{ route('account/update') }}">
                        <img src="{{ $general->getFileUrl($sessionUser->image,'profile') }}" alt class="h-9 w-9 rounded-full object-cover" />
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">{{$sessionUser->first_name.' '.$sessionUser->last_name}}</span>
                            <small class="text-slate-400">{{ $sessionUser->email }}</small>
                        </span>
                    </a>
                    <div class="my-1 border-t border-slate-100"></div>
                    <a class="pjax flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50" href="{{ route('account/update') }}">
                        <i class="bx bx-user"></i> My Account
                    </a>
                    <div class="my-1 border-t border-slate-100"></div>
                    <a class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50" href="{{ route('logout') }}">
                        <i class="bx bx-power-off"></i> Log Out
                    </a>
                </div>
            </div>
            <?php } else { ?>
            <a href="login" class="btn-primary rounded-full pjax" data-pjax-layout="blank">
                Login <i class="bx bx-log-in-circle"></i>
            </a>
            <a href="register" class="btn-primary rounded-full pjax" data-pjax-layout="blank">
                Register <i class="bx bx-user"></i>
            </a>
            <?php } ?>
        </div>
    </div>

    <div id="mobile-menu" class="hidden space-y-1 border-t border-slate-200 px-4 py-3 lg:hidden">
        <a class="pjax block rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50" href="{{ route('home') }}">Home</a>
        <a class="pjax block rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50" href="{{ route('blog') }}">Blog</a>
        <a class="pjax block rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50" href="{{ route('contact') }}">Contact</a>
        <?php if ($sessionUser) { ?>
        <a class="pjax block rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50" href="{{ route('notes') }}">Notes</a>
        <?php } ?>
        <?php if ($sessionUser) { ?>
        <a class="pjax block rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50" href="{{ route('account/update') }}">My Account</a>
        <a class="block rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50" href="{{ route('logout') }}">Log Out</a>
        <?php } else { ?>
        <a href="login" class="block rounded-md px-3 py-2 text-sm font-medium text-primary-600 hover:bg-slate-50 pjax" data-pjax-layout="blank">Login</a>
        <a href="register" class="block rounded-md px-3 py-2 text-sm font-medium text-primary-600 hover:bg-slate-50 pjax" data-pjax-layout="blank">Register</a>
        <?php } ?>
    </div>
</nav>

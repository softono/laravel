<nav class="flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 py-3 sm:px-6 lg:px-8" id="layout-navbar">
    <button type="button" class="text-slate-500 hover:text-slate-700 lg:hidden" data-sidebar-toggle="open" aria-label="Open sidebar">
        <i class="bx bx-menu text-xl"></i>
    </button>

    <div class="flex flex-1 items-center justify-end gap-3">
        <!-- Theme switcher -->
        <div class="relative" data-dropdown>
            <button type="button" class="btn-icon" data-dropdown-toggle>
                <i class="bx bx-sun text-lg" data-theme-icon></i>
            </button>
            <ul data-dropdown-menu class="hidden absolute end-0 z-20 mt-2 w-36 rounded-md border border-slate-200 bg-white py-1 shadow-lg">
                <li>
                    <a href="javascript:void(0);" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-slate-50" data-theme-option="system">
                        <i class="bx bx-desktop"></i> System
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-slate-50" data-theme-option="light">
                        <i class="bx bx-sun"></i> Light
                    </a>
                </li>
                <li>
                    <a href="javascript:void(0);" class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-slate-50" data-theme-option="dark">
                        <i class="bx bx-moon"></i> Dark
                    </a>
                </li>
            </ul>
        </div>
        <!-- / Theme switcher -->

        <!-- User -->
        <div class="relative" data-dropdown>
            <button type="button" class="flex items-center" data-dropdown-toggle>
                <img src="{{ $general->getFileUrl($sessionUser->image, 'profile') }}" alt class="h-9 w-9 rounded-full object-cover" />
            </button>
            <ul data-dropdown-menu class="hidden absolute end-0 z-20 mt-2 w-56 rounded-md border border-slate-200 bg-white py-1 shadow-lg">
                <li>
                    <a class="pjax flex items-center gap-3 px-4 py-2 hover:bg-slate-50" href="{{ route('admin/account/update') }}">
                        <img src="{{ $general->getFileUrl($sessionUser->image, 'profile') }}" alt class="h-9 w-9 rounded-full object-cover" />
                        <span>
                            <span class="block text-sm font-semibold text-slate-800">{{ $sessionUser->first_name . ' ' . $sessionUser->last_name }}</span>
                            <small class="text-slate-400">{{ $sessionUser->email }}</small>
                        </span>
                    </a>
                </li>
                <li class="my-1 border-t border-slate-100"></li>
                <li>
                    <a class="pjax flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50" href="{{ route('admin/account/update') }}">
                        <i class="bx bx-user"></i> My Account
                    </a>
                </li>
                <li>
                    <a class="pjax flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50" href="{{ route('admin/setting/update') }}">
                        <i class="bx bx-cog"></i> Settings
                    </a>
                </li>
                <li class="my-1 border-t border-slate-100"></li>
                <li>
                    <a class="flex items-center gap-3 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50" href="{{ route('admin/auth/logout') }}">
                        <i class="bx bx-power-off"></i> Log Out
                    </a>
                </li>
            </ul>
        </div>
        <!--/ User -->
    </div>
</nav>

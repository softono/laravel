<div class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>

<aside id="layout-menu"
    class="fixed inset-y-0 start-0 z-40 flex w-64 -translate-x-full flex-col bg-slate-900 transition-transform lg:translate-x-0"
    :class="{ '!translate-x-0': sidebarOpen }">
    <div class="flex items-center gap-2 px-4 py-4">
        <a href="{{ route('admin/dashboard') }}" class="pjax flex flex-1 items-center gap-2">
            <img src="{{ $general->getFileUrl(config('setting.app_favicon'),'logo') }}"
                alt="{{ config('setting.app_name') }}" class="h-8 w-8 rounded" />
            <span class="truncate font-bold text-white">{{ config('setting.app_name') }}</span>
        </a>
        <button type="button" class="text-slate-400 hover:text-white lg:hidden" @click="sidebarOpen = false" aria-label="Close sidebar">
            <i class="bx bx-x text-xl"></i>
        </button>
    </div>
    <div class="border-t border-white/10"></div>
    <ul class="flex-1 space-y-1 overflow-y-auto px-3 py-3">
        <li class="menu-item active-menu" data-active_menu_links="admin/dashboard">
            <a href="{{ route('admin/dashboard') }}" class="menu-link pjax">
                <i class="bx bx-home-smile text-lg"></i>
                <div>Dashboard</div>
            </a>
        </li>

        @if($sessionUser->hasPermission('admin/user'))
        <li class="menu-item active-menu" data-active_menu_links="admin/user,admin/user/create,admin/user/view,admin/user/update">
            <a href="{{ route('admin/user') }}" class="menu-link pjax" data-pjax-cache="true">
                <i class="bx bx-user text-lg"></i>
                <div>Users</div>
            </a>
        </li>
        @endif

        @if($sessionUser->hasPermission('admin_blog'))
        <li class="menu-item active-menu" data-active_menu_links="admin/blog,admin/blog/create,admin/blog/update">
            <a href="{{ route('admin/blog') }}" class="menu-link pjax" data-pjax-cache="true">
                <i class="bx bx-news text-lg"></i>
                <div>Blog</div>
            </a>
        </li>
        @endif

        @if($sessionUser->hasPermission(['admin_setting', 'admin_seo', 'admin_admin', 'admin_device', 'admin_activity', 'admin_page', 'admin_emailtemplate']))
        <li class="menu-item active-menu" data-active_menu_class="open">
            <a href="javascript:void(0);" class="menu-link menu-toggle pjax">
                <i class="bx bx-cog text-lg"></i>
                <div>Setting</div>
                <i class="bx bx-chevron-right menu-toggle-icon"></i>
            </a>
            <ul class="menu-sub">
                @if($sessionUser->hasPermission('admin_setting'))
                <li class="menu-item active-menu" data-active_menu_links="admin/setting/update">
                    <a href="{{ route('admin/setting/update') }}" class="menu-link pjax">
                        <div>Setting</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_seo'))
                <li class="menu-item active-menu" data-active_menu_links="admin/seo/create,admin/seo/update,admin/seo/meta">
                    <a href="{{ route('admin/seo/meta') }}" class="menu-link pjax" data-pjax-cache="true" data-active_menu_links="admin/seo/create">
                        <div>Seo Meta</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_admin'))
                <li class="menu-item active-menu" data-active_menu_links="admin/admin,admin/admin/create,admin/admin/view,admin/admin/update">
                    <a href="{{ route('admin/admin') }}" class="menu-link pjax" data-pjax-cache="true">
                        <div>Admin</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_device'))
                <li class="menu-item active-menu" data-active_menu_links="admin/device">
                    <a href="{{ route('admin/device') }}" class="menu-link pjax" data-pjax-cache="true">
                        <div>Device</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_activity'))
                <li class="menu-item active-menu" data-active_menu_links="admin/activity">
                    <a href="{{ route('admin/activity') }}" class="menu-link pjax" data-pjax-cache="true">
                        <div>Activity</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_page'))
                <li class="menu-item active-menu" data-active_menu_links="admin/pages,admin/page/update">
                    <a href="{{ route('admin/page') }}" class="menu-link pjax" data-pjax-cache="true">
                        <div>Pages</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_emailtemplate'))
                <li class="menu-item active-menu" data-active_menu_links="admin/email-template,admin/email-template/update">
                    <a href="{{ route('admin/email-template') }}" class="menu-link pjax" data-pjax-cache="true">
                        <div>Email Template</div>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif

        <li class="menu-item">
            <a href="{{ route('admin/auth/logout') }}" class="menu-link">
                <i class="bx bx-power-off text-lg"></i>
                <div>Logout</div>
            </a>
        </li>
    </ul>
</aside>

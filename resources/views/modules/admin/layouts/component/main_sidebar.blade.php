@php
    // `active` / `open` are toggled on the <li> by pjax.js (updateActiveMenu) and the [data-menu-toggle] handler in app.js.
    $itemClass = '[&.active>a]:bg-sidebar-primary [&.active>a]:text-sidebar-primary-foreground';
    $groupClass = $itemClass . ' [&.open>ul]:block [&.open>a>i:last-child]:rotate-90';
    $linkClass = 'text-sidebar-foreground/70 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors';
@endphp
<div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-black/50 lg:hidden" data-sidebar-toggle="close"></div>

<aside id="layout-menu"
    class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-sidebar text-sidebar-foreground border-sidebar-border border-r transition-transform lg:translate-x-0">
    <div class="flex items-center gap-2 px-4 py-4">
        <a href="{{ route('admin/dashboard') }}" class="pjax flex flex-1 items-center gap-2">
            <img src="{{ $general->getFileUrl(config('setting.app_favicon'),'logo') }}"
                alt="{{ config('setting.app_name') }}" class="h-8 w-8 rounded" />
            <span class="truncate font-bold text-sidebar-foreground">{{ config('setting.app_name') }}</span>
        </a>
        <button type="button" class="text-sidebar-foreground/70 hover:text-sidebar-foreground lg:hidden" data-sidebar-toggle="close" aria-label="Close sidebar">
            <i class="bx bx-x text-xl"></i>
        </button>
    </div>
    <div class="border-sidebar-border border-t"></div>
    <ul class="flex-1 space-y-1 overflow-y-auto px-3 py-3">
        <li class="active-menu {{ $itemClass }}" data-active_menu_links="admin/dashboard">
            <a href="{{ route('admin/dashboard') }}" class="pjax {{ $linkClass }}">
                <i class="bx bx-home-smile text-lg"></i>
                <div>Dashboard</div>
            </a>
        </li>

        @if($sessionUser->hasPermission('admin/user'))
        <li class="active-menu {{ $itemClass }}" data-active_menu_links="admin/user,admin/user/create,admin/user/view,admin/user/update">
            <a href="{{ route('admin/user') }}" class="pjax {{ $linkClass }}" data-pjax-cache="true">
                <i class="bx bx-user text-lg"></i>
                <div>Users</div>
            </a>
        </li>
        @endif

        @if($sessionUser->hasPermission('admin_blog'))
        <li class="active-menu {{ $itemClass }}" data-active_menu_links="admin/blog,admin/blog/create,admin/blog/update">
            <a href="{{ route('admin/blog') }}" class="pjax {{ $linkClass }}" data-pjax-cache="true">
                <i class="bx bx-news text-lg"></i>
                <div>Blog</div>
            </a>
        </li>
        @endif

        @if($sessionUser->hasPermission(['admin_setting', 'admin_seo', 'admin_admin', 'admin_device', 'admin_activity', 'admin_page', 'admin_emailtemplate']))
        <li class="active-menu {{ $groupClass }}" data-active_menu_class="open">
            <a href="javascript:void(0);" class="{{ $linkClass }}" data-menu-toggle>
                <i class="bx bx-cog text-lg"></i>
                <div>Setting</div>
                <i class="bx bx-chevron-right ml-auto transition-transform"></i>
            </a>
            <ul class="mt-1 hidden space-y-1 pl-8">
                @if($sessionUser->hasPermission('admin_setting'))
                <li class="active-menu {{ $itemClass }}" data-active_menu_links="admin/setting/update">
                    <a href="{{ route('admin/setting/update') }}" class="pjax {{ $linkClass }}">
                        <div>Setting</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_seo'))
                <li class="active-menu {{ $itemClass }}" data-active_menu_links="admin/seo/create,admin/seo/update,admin/seo/meta">
                    <a href="{{ route('admin/seo/meta') }}" class="pjax {{ $linkClass }}" data-pjax-cache="true" data-active_menu_links="admin/seo/create">
                        <div>Seo Meta</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_admin'))
                <li class="active-menu {{ $itemClass }}" data-active_menu_links="admin/admin,admin/admin/create,admin/admin/view,admin/admin/update">
                    <a href="{{ route('admin/admin') }}" class="pjax {{ $linkClass }}" data-pjax-cache="true">
                        <div>Admin</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_device'))
                <li class="active-menu {{ $itemClass }}" data-active_menu_links="admin/device">
                    <a href="{{ route('admin/device') }}" class="pjax {{ $linkClass }}" data-pjax-cache="true">
                        <div>Device</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_activity'))
                <li class="active-menu {{ $itemClass }}" data-active_menu_links="admin/activity">
                    <a href="{{ route('admin/activity') }}" class="pjax {{ $linkClass }}" data-pjax-cache="true">
                        <div>Activity</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_page'))
                <li class="active-menu {{ $itemClass }}" data-active_menu_links="admin/pages,admin/page/update">
                    <a href="{{ route('admin/page') }}" class="pjax {{ $linkClass }}" data-pjax-cache="true">
                        <div>Pages</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_emailtemplate'))
                <li class="active-menu {{ $itemClass }}" data-active_menu_links="admin/email-template,admin/email-template/update">
                    <a href="{{ route('admin/email-template') }}" class="pjax {{ $linkClass }}" data-pjax-cache="true">
                        <div>Email Template</div>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif

        <li>
            <a href="{{ route('admin/auth/logout') }}" class="{{ $linkClass }}">
                <i class="bx bx-power-off text-lg"></i>
                <div>Logout</div>
            </a>
        </li>
    </ul>
</aside>

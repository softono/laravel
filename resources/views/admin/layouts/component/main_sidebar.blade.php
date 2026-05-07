<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme" data-bg-class="bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('admin/dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <span class="avatar  fav-side">
                    <img src="{{ $general->getFileUrl(config('setting.app_favicon'),'logo') }}"
                        alt="{{ config('setting.app_name') }}" class="rounded h-px-38" />
                </span>
            </span>
            <!--<span class="app-brand-text demo menu-text fw-bold ms-2"><img src="{{ $general->getFileUrl(config('setting.app_favicon'),'logo') }}"-->
            <!--            alt="{{ config('setting.app_name') }}" class="rounded h-px-38 demo-img-logo " />{{ config('setting.app_name') }}</span>-->
                        <span class="app-brand-text demo menu-text fw-bold">{{ config('setting.app_name') }}</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="icon-base bx bx-chevron-left"></i>
        </a>
    </div>
    <div class="menu-divider mt-0"></div>
    <div class="menu-inner-shadow" style="display: none;"></div>
    <ul class="menu-inner py-1 ps ps--active-y">
        <li class="menu-item active-menu" data-active_menu_links="admin/dashboard">
            <a href="{{ route('admin/dashboard') }}" class="menu-link pjax">
                <i class="menu-icon icon-base bx bx-home-smile"></i>
                <div data-i18n="Dashboard">Dashboard</div>
            </a>
        </li>
     
        @if($sessionUser->hasPermission('admin/user'))
        <li
            class="menu-item active-menu" data-active_menu_links="admin/user,admin/user/create,admin/user/view,admin/user/update">
            <a href="{{ route('admin/user') }}" class="menu-link pjax" data-pjax-cache="true" >
                <i class="menu-icon icon-base bx bx-user"></i>
                <div data-i18n="Users">Users</div>
            </a>
        </li>
        @endif
        @if($sessionUser->hasPermission(['admin_setting', 'admin_seo', 'admin_admin', 'admin_device', 'admin_user_activity', 'admin_page', 'admin_emailtemplate']))
        <li
            class="menu-item active-menu" data-active_menu_class="open">
            <a href="javascript:void(0);"
                class="menu-link menu-toggle pjax">
                <i class="menu-icon icon-base bx bx-cog"></i>
                <div data-i18n="Setting">Setting</div>
            </a>
            <ul class="menu-sub">
                @if($sessionUser->hasPermission('admin_setting'))
                <li class="menu-item active-menu" data-active_menu_links="admin/setting/update">
                    <a href="{{ route('admin/setting/update') }}" class="menu-link pjax">
                        <div data-i18n="Setting">Setting</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_seo'))
                <li
                    class="menu-item active-menu" data-active_menu_links="admin/seo/create,admin/seo/update,admin/seo/meta">
                    <a href="{{ route('admin/seo/meta') }}" class="menu-link pjax" data-pjax-cache="true" data-active_menu_links="admin/seo/create">
                        <div data-i18n="Seo Meta">Seo Meta</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_admin'))
                <li
                    class="menu-item active-menu" data-active_menu_links="admin/admin,admin/admin/create,admin/admin/view,admin/admin/update">
                    <a href="{{ route('admin/admin') }}" class="menu-link pjax" data-pjax-cache="true">
                        <div data-i18n="Admin">Admin</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_device'))
                <li class="menu-item active-menu" data-active_menu_links="admin/device">
                    <a href="{{ route('admin/device') }}" class="menu-link pjax" data-pjax-cache="true">
                        <div data-i18n="Device">Device</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_user_activity'))
                <li class="menu-item active-menu" data-active_menu_links="admin/user-activity">
                    <a href="{{ route('admin/user-activity') }}" class="menu-link pjax" data-pjax-cache="true">
                        <div data-i18n="Activity">Activity</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_page'))
                <li class="menu-item active-menu" data-active_menu_links="admin/pages,admin/page/update">
                    <a href="{{ route('admin/page') }}" class="menu-link pjax" data-pjax-cache="true">
                        <div data-i18n="Pages">Pages</div>
                    </a>
                </li>
                @endif

                @if($sessionUser->hasPermission('admin_emailtemplate'))
                <li class="menu-item active-menu" data-active_menu_links="admin/email-template,admin/email-template/update">
                    <a href="{{ route('admin/email-template') }}" class="menu-link pjax" data-pjax-cache="true">
                        <div data-i18n="Email Template">Email Template</div>
                    </a>
                </li>
                @endif
            </ul>
        </li>
        @endif
        <li class="menu-item">
            <a href="{{ route('admin/auth/logout') }}" class="menu-link">
                <i class="menu-icon" style="font-size:12px;"><svg class="dropdown-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:22px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg></i>
                <div>Logout</div>
            </a>
        </li>
    </ul>
</aside>
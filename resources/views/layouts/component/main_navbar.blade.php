<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="navbar-nav-right d-flex align-items-center justify-content-end ">
        <a href="{{ route('home') }}" class="app-brand-link gap-1 pjax">
            <span class="avatar me-2">
                <img src="{{ $general->getFileUrl(config('setting.app_logo'),'logo')}}"
                    alt="{{ config('setting.app_name') }}" class="rounded" />
            </span>
            <span class="app-brand-text demo menu-text fw-bold text-heading">{{ config('setting.app_name') }}</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse ms-8" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-1">
                <li class="nav-item active-menu" data-active_menu_links="home">
                    <a class="nav-link pjax" aria-current="page"
                        href="{{ route('home') }}">Home</a>
                </li>
                <li class="nav-item active-menu" data-active_menu_links="contact">
                    <a class="nav-link  pjax" data-pjax-cache="true"
                        href="{{ route('contact') }} ">Contact</a>
                </li>
            </ul>
            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                <ul class="navbar-nav flex-row align-items-center ms-auto">
                    <!-- User -->
                    <?php if ($sessionUser) { ?>
                    <li class="nav-item navbar-dropdown dropdown-user dropdown">
                        <a class="nav-link dropdown-toggle hide-arrow p-0 pjax" href="javascript:void(0);"
                            data-bs-toggle="dropdown">
                            <div class="avatar avatar-online">
                                <img src="{{ $general->getFileUrl($sessionUser->image,'profile') }}" alt
                                    class="w-px-40 h-auto rounded-circle" />
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item pjax" href="{{ route('account/update') }}">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar avatar-online">
                                                <img src="{{ $general->getFileUrl($sessionUser->image,'profile') }}" alt
                                                    class="rounded-circle" />
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <span
                                                class="fw-semibold d-block">{{$sessionUser->first_name.' '.$sessionUser->last_name}}</span>
                                            <small class="text-muted">{{ $sessionUser->email }}</small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                            </li>
                            <li>
                                <a class="dropdown-item pjax" href="{{ route('account/update') }}">
                                    <i class="icon-base bx bx-user icon-md me-3"></i>
                                    <span class="align-middle">My Account</span>
                                </a>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}">
                                    <i class="icon-base bx bx-power-off icon-md me-3"></i>
                                    <span class="align-middle">Log Out</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php } else { ?>
                    <li class="menu-item {{ $general->routeMatchClass('login')}}">
                        <a href="login" class="menu-link btn rounded-pill btn-primary text-white pjax" data-pjax-layout="blank">
                            <div data-i18n="Login">Login
                                <span class="icon-base bx bx-log-in-circle icon-sm "></span>
                            </div>
                        </a>
                    </li>
                    <br>
                    <li class="menu-item {{ $general->routeMatchClass('register')}} ms-2">
                        <a href="register" class="menu-link btn rounded-pill btn-primary text-white pjax" data-pjax-layout="blank">
                            <div data-i18n="Register">Register <span class="icon-base bx bx-user icon-sm "></span></div>
                        </a>
                    </li>
                    <?php } ?>
                    <!--/ User -->
                </ul>
            </div>
        </div>
    </div>
</nav>




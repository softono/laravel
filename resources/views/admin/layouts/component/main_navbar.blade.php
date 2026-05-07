<nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
            <i class="icon-base bx bx-menu icon-md me-3"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
        <ul class="navbar-nav flex-row align-items-center ms-md-auto">
            <!-- User -->
            <li class="nav-item lh-1 me-4"></li>
            <!-- Style Switcher pro feature-->
            <li class="nav-item dropdown me-2 me-xl-0">
                <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);"
                    data-bs-toggle="dropdown">
                    <i class="icon-base bx bx-sun icon-md theme-icon-active"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" id="nav-theme-text">
                    <li>
                        <a class="dropdown-item align-items-center" href="javascript:void(0);"
                            data-bs-theme-value="system">
                            <span class="align-middle"><i class="icon-base bx bx-desktop icon-md me-3"
                                    data-icon="desktop"></i>System</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item align-items-center" href="javascript:void(0);"
                            data-bs-theme-value="light">
                            <span class="align-middle"><i class="icon-base bx bx-sun icon-md me-3 light-style"
                                    data-icon="sun"></i>Light</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item align-items-center" href="javascript:void(0);"
                            data-bs-theme-value="dark">
                            <span class="align-middle"><i class="icon-base bx bx-moon icon-md me-3 dark-style"
                                    data-icon="moon"></i>Dark</span>
                        </a>
                    </li>

                </ul>
            </li>
            <!-- / Style Switcher-->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ $general->getFileUrl($sessionUser->image, 'profile') }}" alt
                            class="w-px-40 h-auto rounded-circle" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" data-bs-popper="static">
                    <li>
                        <a class="dropdown-item pjax" href="{{ route('admin/account/update') }}">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ $general->getFileUrl($sessionUser->image, 'profile') }}" alt
                                            class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $sessionUser->first_name . ' ' . $sessionUser->last_name }}</h6>
                                    <small class="text-body-secondary">{{ $sessionUser->email }}</small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item pjax" href="{{ route('admin/account/update') }}">
                            <i class="icon-base bx bx-user icon-md me-3"></i>
                            <span class="align-middle">My Account</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item pjax" href="{{ route('admin/setting/update') }}">
                            <i class="icon-base bx bx-cog icon-md me-3"></i>
                            <span class="align-middle">Settings</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin/auth/logout') }}">
                            <i class="icon-base bx bx-power-off icon-md me-3"></i>
                            <span class="align-middle">Log Out</span>
                        </a>
                    </li>
                </ul>
            </li>
            <!--/ User -->
        </ul>
    </div>
</nav>

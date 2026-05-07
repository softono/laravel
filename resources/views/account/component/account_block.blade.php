<!-- Navbar pills -->

<div class="nav-align-top pt-3">
    <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-md-0 gap-2">
        <li class="nav-item ">
            <a class="nav-link pjax {{ $general->routeMatchClass('account/update') }}" href="account/update"><i
                    class="icon-base bx bx-user icon-sm me-1_5"></i>Account</a>
        </li>
        <li class="nav-item">
            <a class="nav-link pjax {{ $general->routeMatchClass('account/password-change') }}"
                href="account/password-change"><i class="icon-base bx bx-key icon-sm me-1_5"></i> Password Change</a>
        </li>

        <li class="nav-item">
            <a class="nav-link pjax {{ $general->routeMatchClass('account/tfa') }}" href="account/tfa"><i
                    class="icon-base bx bx-lock-alt icon-sm me-1_5"></i> Two Factor Authentication</a>
        </li>

        <li class="nav-item">
            <a class="nav-link pjax {{ $general->routeMatchClass('account/device') }}"
                href="{{ route('account/device') }}"><i class="icon-base bx bx-lock-alt icon-sm me-1_5"></i> Device</a>
        </li>
        <li class="nav-item">
            <a class="nav-link pjax {{ $general->routeMatchClass('account/user-activity') }} "
                href="{{ route('account/user-activity') }}"><i class="icon-base bx bx-history icon-sm me-1_5"></i>
                Activity</a>
        </li>
    </ul>
</div>

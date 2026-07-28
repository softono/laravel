<!-- Navbar pills -->

<div class="nav-align-top pt-3">
    <ul class="nav nav-pills flex-column flex-md-row mb-6 gap-md-0 gap-2">
        <li class="nav-item">
            <a class="nav-link pjax {{ $general->routeMatchClass('account/update') }}" href="{{ route('account/update') }}"><i
                    class="icon-base bx bx-user icon-sm me-1_5"></i>Account</a>
        </li>
        <li class="nav-item">
            <a class="nav-link pjax {{ $general->routeMatchClass('account/password-change') }}"
                href="{{ route('account/password-change') }}"><i class="icon-base bx bx-key icon-sm me-1_5"></i> Password Change</a>
        </li>
        <li class="nav-item">
            <a class="nav-link pjax {{ $general->routeMatchClass('account/tfa') }}" href="{{ route('account/tfa') }}"><i
                    class="icon-base bx bx-lock-alt icon-sm me-1_5"></i> Two Factor Authentication</a>
        </li>
        <li class="nav-item">
            <a class="nav-link pjax {{ $general->routeMatchClass('account/passkeys') }}"
                href="{{ route('account/passkeys') }}"><i class="icon-base bx bx-key icon-sm me-1_5"></i> Passkeys</a>
        </li>
        <li class="nav-item">
            <a class="nav-link pjax {{ $general->routeMatchClass('account/session') }}"
                href="{{ route('account/session') }}"><i class="icon-base bx bx-devices icon-sm me-1_5"></i> Sessions</a>
        </li>
        <li class="nav-item">
            <a class="nav-link pjax {{ $general->routeMatchClass('account/user-activity') }}"
                href="{{ route('account/user-activity') }}"><i class="icon-base bx bx-history icon-sm me-1_5"></i>
                Activity</a>
        </li>
    </ul>
</div>

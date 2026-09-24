<!-- Navbar pills -->

<div class="mb-4">
    <ul class="flex flex-wrap gap-2 border-b border-border pb-2">
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-foreground {{ $general->routeMatchClass($prefix.'account/update') }}"
                href="{{ route($prefix.'account/update') }}"><i class="bx bx-user"></i>Account</a>
        </li>
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-foreground {{ $general->routeMatchClass($prefix.'account/password-change') }}"
                href="{{ route($prefix.'account/password-change') }}"><i class="bx bx-key"></i> Password Change</a>
        </li>
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-foreground {{ $general->routeMatchClass($prefix.'account/two-factor') }}"
                href="{{ route($prefix.'account/two-factor') }}"><i class="bx bx-lock-alt"></i> Two Factor Authentication</a>
        </li>
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-foreground {{ $general->routeMatchClass($prefix.'account/passkeys') }}"
                href="{{ route($prefix.'account/passkeys') }}"><i class="bx bx-key"></i> Passkeys</a>
        </li>
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-foreground {{ $general->routeMatchClass($prefix.'account/session') }}"
                href="{{ route($prefix.'account/session') }}"><i class="bx bx-devices"></i> Sessions</a>
        </li>
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-foreground {{ $general->routeMatchClass($prefix.'account/user-activity') }}"
                href="{{ route($prefix.'account/user-activity') }}"><i class="bx bx-history"></i>
                Activity</a>
        </li>
    </ul>
</div>

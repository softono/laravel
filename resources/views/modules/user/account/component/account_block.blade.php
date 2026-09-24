<!-- Navbar pills -->

<div class="mb-4">
    <ul class="flex flex-wrap gap-2 border-b border-slate-200 pb-2">
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-primary-600 {{ $general->routeMatchClass('account/update') }}"
                href="{{ route('account/update') }}"><i class="bx bx-user"></i>Account</a>
        </li>
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-primary-600 {{ $general->routeMatchClass('account/password-change') }}"
                href="{{ route('account/password-change') }}"><i class="bx bx-key"></i> Password Change</a>
        </li>
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-primary-600 {{ $general->routeMatchClass('account/two-factor') }}"
                href="{{ route('account/two-factor') }}"><i class="bx bx-lock-alt"></i> Two Factor Authentication</a>
        </li>
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-primary-600 {{ $general->routeMatchClass('account/passkeys') }}"
                href="{{ route('account/passkeys') }}"><i class="bx bx-key"></i> Passkeys</a>
        </li>
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-primary-600 {{ $general->routeMatchClass('account/session') }}"
                href="{{ route('account/session') }}"><i class="bx bx-devices"></i> Sessions</a>
        </li>
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-primary-600 {{ $general->routeMatchClass('account/user-activity') }}"
                href="{{ route('account/user-activity') }}"><i class="bx bx-history"></i>
                Activity</a>
        </li>
    </ul>
</div>

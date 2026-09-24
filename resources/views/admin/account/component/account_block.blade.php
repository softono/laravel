<?php
$model = auth()->user();
?>
<!-- Navbar pills -->
<div class="mb-4">
    <ul class="flex flex-wrap gap-2 border-b border-slate-200 pb-2">
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-primary-600 {{ $general->routeMatchClass('admin/account/update') }}"
                href="{{ route('admin/account/update') }}"><i class="bx bx-user"></i> Account</a>
        </li>
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-primary-600 {{ $general->routeMatchClass('admin/account/password-change') }}"
                href="{{ route('admin/account/password-change') }}"><i class="bx bx-key"></i> Password Change</a>
        </li>
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-primary-600 {{ $general->routeMatchClass('admin/account/tfa') }}"
                href="{{ route('admin/account/tfa') }}"><i class="bx bx-lock-alt"></i> Two Factor Authentication</a>
        </li>
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-primary-600 {{ $general->routeMatchClass('admin/account/device') }}"
                href="{{ route('admin/account/device') }}"><i class="bx bx-devices"></i> Device</a>
        </li>
        <li>
            <a class="pjax inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-primary-600 {{ $general->routeMatchClass('admin/account/user-activity') }}"
                href="{{ route('admin/account/user-activity') }}"><i class="bx bx-history"></i> Log</a>
        </li>
    </ul>
</div>

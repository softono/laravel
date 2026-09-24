@extends('modules.admin.layouts.main')
@section('title', 'Profile')
@section('content')
<div>
    <?= view('admin/account/component/account_block',compact('model')); ?>
    <div class="space-y-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Two-steps verification</h5>
            </div>
            <div class="card-body">
                @if($model && $model->status_tfa)
                <p class="mb-4 text-sm text-slate-600">Two factor authentication is disabled.</p>
                <button
                    data-action="{{ route('admin/account/tfa-status-change') }}"
                    onclick="app.confirmAction(this);"
                    class="btn-primary">
                   Enable Two-Factor Authentication
                </button>
                @else
                <p class="mb-4 text-sm text-slate-600">Two factor authentication is enabled.</p>
                <button
                    data-action="{{ route('admin/account/tfa-status-change') }}"
                    onclick="app.confirmAction(this);"
                    class="btn-primary">
                    Disable Two-Factor Authentication
                </button>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Devices That Don't Need a Second Step</h5>
            </div>
            <div class="card-body">
                <p class="mb-4 text-sm text-slate-600">You can skip the second step on devices you trust, such as your own computer.</p>
                <div class="relative rounded-md bg-slate-50 p-4">
                    <div class="flex items-center">
                        <div class="shrink-0">
                            <i class="me-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-devices-check" width="50" height="50" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" >
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M13 15.5v-6.5a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1v4" />
                                    <path d="M18 8v-3a1 1 0 0 0 -1 -1h-13a1 1 0 0 0 -1 1v12a1 1 0 0 0 1 1h7" />
                                    <path d="M16 9h2" />
                                    <path d="M15 19l2 2l4 -4" />
                                </svg>
                            </i>
                        </div>
                        <div class="flex grow flex-wrap items-center justify-between gap-3">
                            <div>
                                <h5 class="mb-0 me-3 text-sm font-semibold text-slate-800">Device You Trust</h5>
                                <p class="me-3 mb-0 text-sm font-medium text-slate-600">Revoke trusted status from your device that skips 2-Step Verification.</p>
                            </div>
                            <div class="text-end">
                                <button onclick="app.confirmAction(this);"
                                        data-action="{{ route('admin/account/revoke-all') }}"
                                        class="btn-primary"
                                        title="Revoke All">
                                        REVOKE ALL
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

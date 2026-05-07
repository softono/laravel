@extends('layouts.main')
@section('title', 'Profile')
@section('content')
    <div class="row">
        <div class="col-md-12">
            {{ view('account/component/account_block', compact('model')) }}
            <div class="col-lg-12 col-md-12">
                <div class="edit-profile_wrapper">
                    <div class="main-card mb-3 card">
                        <div class="card-header">
                            <h5 class="mb-6">Two-steps verification</h5>
                            @if ($model && $model->status_tfa)
                                <h5 class="mb-4 text-body">Two factor authentication is enabled.</h5>

                                <button data-action="{{ route('account/tfa-status-change') }}"
                                    onclick="app.confirmAction(this);" class="btn btn-danger mt-2">
                                    Disable Two-Factor Authentication
                                </button>
                            @else
                                <h5 class="mb-4 text-body">Two factor authentication is disabled.</h5>

                                <button data-action="{{ route('account/tfa-status-change') }}"
                                    onclick="app.confirmAction(this);" class="btn btn-primary mt-2">
                                    Enable Two-Factor Authentication
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="main-card mb-3 card">
                        <div class="card-header">
                            <h5 class="mb-6">Authenticator App</h5>
                            <div class="d-flex flex-wrap gap-3 align-items-center">
                                @if ($model->totp_secret_key)
                                    <a onclick="app.confirmAction(this)" data-action="{{ route('remove-totp') }}"
                                        class="btn btn-danger text-white pjax" tabindex="0">
                                        <span class="d-none d-sm-block">Remove Authenticator</span>
                                        <i class="icon-base bx bx-x d-block d-sm-none"></i>
                                    </a>
                                    @if ($model->backup_code)
                                        <a onclick="app.showModalView('{{ route('backup-code') }}')"
                                            class="btn btn-primary text-white pjax" tabindex="0">
                                            <span class="d-none d-sm-block">Backup Code</span>
                                            <i class="icon-base bx bx-download d-block d-sm-none"></i>
                                        </a>
                                    @endif
                                @else
                                    <a onclick="app.showModalView('{{ route('get-qr-modal', $model->id) }}')"
                                        class="btn btn-primary text-white pjax" tabindex="0">
                                        <span class="d-none d-sm-block">Add Authenticator App</span>
                                        <i class="icon-base bx bx-upload d-block d-sm-none"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="main-card mb-3 card">
                        <div class="card-header">
                            <h5 class="mb-6">Devices That Don't Need a Second Step</h5>
                            <h5 class="mb-4 text-body">You can skip the second step on devices you trust, such as your own
                                computer.</h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="datatable-list-table table border-top" id="data-table">
                                        <thead>
                                            <tr>
                                                <th>Client</th>
                                                <th>Location</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                            @if (isset($trustedDevices) && count($trustedDevices) > 0)

                                                @foreach ($trustedDevices as $device)
                                                    <tr>
                                                        <td>{{ substr($device, 0, 20) }}...</td>
                                                        <td>Trusted Device</td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="2">No trusted devices found</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="bg-lighter rounded p-4 mb-4 position-relative">
                                    <div class="d-flex align-items-center2">
                                        <div class="flex-shrink-0">
                                            <i class="me-4">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-devices-check" width="50"
                                                    height="50" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M13 15.5v-6.5a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1v4" />
                                                    <path
                                                        d="M18 8v-3a1 1 0 0 0 -1 -1h-13a1 1 0 0 0 -1 1v12a1 1 0 0 0 1 1h7" />
                                                    <path d="M16 9h2" />
                                                    <path d="M15 19l2 2l4 -4" />
                                                </svg>
                                            </i>
                                        </div>
                                        <div class="flex-grow-1 d-flex align-items-center justify-content-between">
                                            <div class="mb-sm-0 mb-2 ">
                                                <h5 class="mb-0 me-3">Device You Trust</h5>
                                                <p class="me-3 mb-0 fw-medium">Revoke trusted status from your device that
                                                    skips 2-Step Verification.</p>
                                            </div>
                                            <div class="text-end">
                                                <button onclick="app.confirmAction(this);"
                                                    data-action="{{ route('account/revoke-all') }}"
                                                    class="btn btn-primary text-white" title="Revoke All">
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
            </div>
        </div>
    </div>
    </div>
@endsection

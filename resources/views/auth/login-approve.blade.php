@extends('layouts.blank')
@section('title')
    Approve Login
@endsection
@section('content')
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-6">
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <div class="app-brand justify-content-center">
                            <a href="{{ url('/') }}" class="app-brand-link d-flex align-items-center">
                                <span class="app-brand-logo demo">
                                    <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                                        class="brand-image img-circle elevation-3 preview-app-logo" style="height: 50px;">
                                </span>
                                <span class="app-brand-text demo text-heading fw-bold">{{ config('setting.app_name') }}</span>
                            </a>
                        </div>

                        <div id="approve-loading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>

                        <div id="approve-content" style="display:none;" class="text-center">
                            <h4 class="mb-1">Confirm Login</h4>
                            <p class="mb-3">Someone is trying to log in as <strong id="approve-email"></strong> on:</p>
                            <p class="mb-3"><strong id="approve-device"></strong></p>
                            <p class="mb-1">Confirm this code matches the one shown on the other device:</p>
                            <p class="mb-4">
                                <span id="approve-code" style="font-family:monospace;font-size:1.5rem;letter-spacing:0.2em;background:#f5f5f5;border-radius:999px;padding:0.4rem 1.2rem;display:inline-block;"></span>
                            </p>
                            <div class="d-flex gap-2 justify-content-center">
                                <button type="button" class="btn btn-primary" id="approve-btn">Approve</button>
                                <button type="button" class="btn btn-outline-danger" id="reject-btn">Reject</button>
                            </div>
                        </div>

                        <div id="approve-result" style="display:none;" class="text-center py-4">
                            <p id="approve-result-message" class="mb-0"></p>
                        </div>

                        <div id="approve-invalid" style="display:none;" class="text-center py-4">
                            <p class="text-danger mb-0">This login link is no longer valid.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/auth/approve.js') }}"></script>
    <script>
        loginApprove.init({
            infoUrl: '{{ url('/api/auth/login-link/approve') }}',
            id: '{{ request('id') }}',
            token: '{{ request('token') }}',
        });
    </script>
@endpush

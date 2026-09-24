@extends('layouts.blank')
@section('title')
    Approve Login
@endsection
@section('content')
    <div class="w-full max-w-md">
        <div class="card">
            <div class="card-body p-6 sm:p-8">
                <div class="mb-6 flex justify-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <img src="{{ $general->getFileUrl(config('setting.app_logo'), 'logo') }}"
                            class="h-10 w-10 rounded-full object-cover" alt="">
                        <span class="text-lg font-bold text-slate-800">{{ config('setting.app_name') }}</span>
                    </a>
                </div>

                <div id="approve-loading" class="py-4 text-center">
                    <span class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-primary-200 border-t-primary-600"></span>
                </div>

                <div id="approve-content" style="display:none;" class="text-center">
                    <h4 class="mb-1 text-xl font-semibold text-slate-800">Confirm Login</h4>
                    <p class="mb-3">Someone is trying to log in as <strong id="approve-email"></strong> on:</p>
                    <p class="mb-3"><strong id="approve-device"></strong></p>
                    <p class="mb-1">Confirm this code matches the one shown on the other device:</p>
                    <p class="mb-4">
                        <span id="approve-code" style="font-family:monospace;font-size:1.5rem;letter-spacing:0.2em;background:#f5f5f5;border-radius:999px;padding:0.4rem 1.2rem;display:inline-block;"></span>
                    </p>
                    <div class="flex justify-center gap-2">
                        <button type="button" class="btn-primary" id="approve-btn">Approve</button>
                        <button type="button" class="btn-outline text-rose-600 border-rose-300 hover:bg-rose-50" id="reject-btn">Reject</button>
                    </div>
                </div>

                <div id="approve-result" style="display:none;" class="py-4 text-center">
                    <p id="approve-result-message" class="mb-0"></p>
                </div>

                <div id="approve-invalid" style="display:none;" class="py-4 text-center">
                    <p class="mb-0 text-rose-600">This login link is no longer valid.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/auth/approve.js') }}"></script>
    <script>
        loginApprove.init({
            infoUrl: '{{ url('/auth/login-link/approve') }}',
            id: '{{ request('id') }}',
            token: '{{ request('token') }}',
        });
    </script>
@endpush

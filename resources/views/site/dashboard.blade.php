@extends('layouts.main')
@section('title')
Home
@endsection
@section('content')
<h4 class="text-xl font-bold py-3 mb-4">Dashboard</h4>
<div class="grid grid-cols-1 gap-6 mb-12 md:grid-cols-2 lg:grid-cols-3">
    <div class="card flex h-full flex-col">
        <div class="flex h-48 w-full items-center justify-center rounded-t-lg bg-slate-100">
            <i class="bx bx-image text-5xl text-slate-400"></i>
        </div>
        <div class="card-body flex flex-1 flex-col">
            <h5 class="card-title">Card title</h5>
            <p class="mt-2 flex-1 text-sm text-slate-600">
                Some quick example text to build on the card title and make up the bulk of the card's content.
            </p>
            <a href="javascript:void(0)" class="btn-outline mt-4 self-start">Go somewhere</a>
        </div>
    </div>
    <div class="card flex h-full flex-col">
        <div class="flex h-48 w-full items-center justify-center rounded-t-lg bg-slate-100">
            <i class="bx bx-image text-5xl text-slate-400"></i>
        </div>
        <div class="card-body flex flex-1 flex-col">
            <h5 class="card-title">Card title</h5>
            <p class="mt-2 flex-1 text-sm text-slate-600">
                Some quick example text to build on the card title and make up the bulk of the card's content.
            </p>
            <a href="javascript:void(0)" class="btn-outline mt-4 self-start">Go somewhere</a>
        </div>
    </div>
    <div class="card flex h-full flex-col">
        <div class="flex h-48 w-full items-center justify-center rounded-t-lg bg-slate-100">
            <i class="bx bx-image text-5xl text-slate-400"></i>
        </div>
        <div class="card-body flex flex-1 flex-col">
            <h5 class="card-title">Card title</h5>
            <p class="mt-2 flex-1 text-sm text-slate-600">
                Some quick example text to build on the card title and make up the bulk of the card's content.
            </p>
            <a href="javascript:void(0)" class="btn-outline mt-4 self-start">Go somewhere</a>
        </div>
    </div>
</div>

<h5 class="text-lg font-bold py-3 mb-2">Account Security</h5>
<div class="grid grid-cols-1 gap-6 mb-12 md:grid-cols-2 lg:grid-cols-3">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Two-Factor Authentication</h5>
            <p class="mt-2 text-sm text-slate-600">Manage your 2FA security settings and authenticator app setup.</p>
            <a href="{{ route('account/two-factor') }}" class="btn-primary pjax mt-4 inline-flex">Manage 2FA</a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Passkeys</h5>
            <p class="mt-2 text-sm text-slate-600">Manage your passwordless security keys and biometric sign-ins.</p>
            <a href="{{ route('account/passkeys') }}" class="btn-primary pjax mt-4 inline-flex">Manage Passkeys</a>
        </div>
    </div>
</div>
@endsection

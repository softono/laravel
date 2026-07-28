@extends('layouts.main')
@section('title')
Home
@endsection
@section('content')
<h4 class="fw-bold py-3 mb-4">Dashboard</h4>
<div class="row mb-12 g-6">
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <img class="card-img-top" src="theme/assets/img/elements/2.png" alt="Card image cap">
            <div class="card-body">
                <h5 class="card-title">Card title</h5>
                <p class="card-text">
                    Some quick example text to build on the card title and make up the bulk of the card's content.
                </p>
                <a href="javascript:void(0)" class="btn btn-outline-primary">Go somewhere</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <img class="card-img-top" src="theme/assets/img/elements/5.png" alt="Card image cap">
            <div class="card-body">
                <h5 class="card-title">Card title</h5>
                <p class="card-text">
                    Some quick example text to build on the card title and make up the bulk of the card's content.
                </p>
                <a href="javascript:void(0)" class="btn btn-outline-primary">Go somewhere</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <img class="card-img-top" src="theme/assets/img/elements/4.png" alt="Card image cap">
            <div class="card-body">
                <h5 class="card-title">Card title</h5>
                <p class="card-text">
                    Some quick example text to build on the card title and make up the bulk of the card's content.
                </p>
                <a href="javascript:void(0)" class="btn btn-outline-primary">Go somewhere</a>
            </div>
        </div>
    </div>
</div>

<h5 class="fw-bold py-3 mb-2">Account Security</h5>
<div class="row mb-12 g-6">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Two-Factor Authentication</h5>
                <p class="card-text">Manage your 2FA security settings and authenticator app setup.</p>
                <a href="{{ route('account/two-factor') }}" class="btn btn-primary pjax">Manage 2FA</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Passkeys</h5>
                <p class="card-text">Manage your passwordless security keys and biometric sign-ins.</p>
                <a href="{{ route('account/passkeys') }}" class="btn btn-primary pjax">Manage Passkeys</a>
            </div>
        </div>
    </div>
</div>
@endsection
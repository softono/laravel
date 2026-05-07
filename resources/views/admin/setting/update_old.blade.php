@extends('admin.layouts.main')
@section('title')
    Setting Update
@endsection
@section('content')
    <div class="breadcrumb-box">
        <h4 class="fw-bold py-3 mb-4">Setting</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="admin/dashboard" class="pjax">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Setting</li>
            </ol>
        </nav>
    </div>
    <div class="content-wrapper">
        <!-- Content -->

        <!-- Tabs -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-start">
                        <h5 class="m-0" style="padding: 21px;">Setting Update</h5>
                        <form action="{{ route('admin/setting/cache-clear') }}"
                            style="padding-top: 25px;padding-right: 15px;">
                            <button type="submit" class="btn btn-primary">
                                Clear Cache
                            </button>
                        </form>
                    </div>
                    <div>
                        <div class="nav-align-top nav-tabs-shadow mb-6">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-general" aria-controls="navs-top-general"
                                        aria-selected="true">
                                        General
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-mail" aria-controls="navs-top-mail" aria-selected="false">
                                        Mail
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-logo" aria-controls="navs-top-logo" aria-selected="false">
                                        Logo
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-recaptcha" aria-controls="navs-top-recaptcha"
                                        aria-selected="false">
                                        Google recaptcha
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-login" aria-controls="navs-top-login"
                                        aria-selected="false">
                                        Social Login
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-content" aria-controls="navs-top-content"
                                        aria-selected="false">
                                        Content
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-payment" aria-controls="navs-top-payment"
                                        aria-selected="false">
                                        Payment
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content" id="custom-tabs-one-tabContent">
                                <div class="tab-pane fade show active" id="navs-top-general" role="tabpanel"
                                    aria-labelledby="navs-top-general">
                                    <form action="{{ route('admin/setting/save') }}" class="ajax-form" method="post">
                                        {{ csrf_field() }}
                                        <div class="form-row row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">App Name <span class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" class="form-control" placeholder="App Name"
                                                            name="app_name" value="{{ config('setting.app_name') }}"
                                                            required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Admin Contact Email <span
                                                            class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="email" class="form-control"
                                                            placeholder="Admin Contact Email" name="admin_email"
                                                            value="{{ config('setting.admin_email') }}" required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Date Format</label>
                                                    <select class="form-control"
                                                        value="{{ config('setting.date_format') }}" name="date_format">
                                                        <option value="Y-m-d"
                                                            <?= config('setting.date_format') == 'Y-m-d' ? 'selected' : '' ?>>
                                                            {{ date('Y-m-d') }}</option>
                                                        <option value="d-m-Y"
                                                            <?= config('setting.date_format') == 'd-m-Y' ? 'selected' : '' ?>>
                                                            {{ date('d-m-Y') }}</option>
                                                        <option value="m-d-Y"
                                                            <?= config('setting.date_format') == 'm-d-Y' ? 'selected' : '' ?>>
                                                            {{ date('m-d-Y') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Timezone</label>
                                                    <select class="form-control" value="{{ config('setting.timezone') }}"
                                                        name="timezone">
                                                        @foreach ($timezonelist as $timezone)
                                                            <option value="{{ $timezone }}"
                                                                <?= config('app.timezone') == $timezone ? 'selected' : '' ?>>
                                                                {{ $timezone }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Date Time Format</label>
                                                    <select class="form-control"
                                                        value="{{ config('setting.date_time_format') }}"
                                                        name="date_time_format">
                                                        <option value="Y-m-d h:i A"
                                                            <?= config('setting.date_time_format') == 'Y-m-d h:i A' ? 'selected' : '' ?>>
                                                            {{ date('Y-m-d h:i A') }}</option>
                                                        <option value="d-m-Y h:i A"
                                                            <?= config('setting.date_time_format') == 'd-m-Y h:i A' ? 'selected' : '' ?>>
                                                            {{ date('d-m-Y h:i A') }}</option>
                                                        <option value="m-d-Y h:i A"
                                                            <?= config('setting.date_time_format') == 'm-d-Y h:i A' ? 'selected' : '' ?>>
                                                            {{ date('m-d-Y h:i A') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Login With OTP</label>
                                                    <select class="form-control"
                                                        value="{{ config('setting.user_login_with_otp') }}"
                                                        name="user_login_with_otp">
                                                        <option value="1"
                                                            <?= config('setting.user_login_with_otp') == '1' ? 'selected' : '' ?>>
                                                            Enable</option>
                                                        <option value="0"
                                                            <?= config('setting.user_login_with_otp') == '0' ? 'selected' : '' ?>>
                                                            Disable</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Email Verify</label>
                                                    <select class="form-control"
                                                        value="{{ config('setting.user_email_verify') }}"
                                                        name="user_email_verify">
                                                        <option value="1"
                                                            <?= config('setting.user_email_verify') == '1' ? 'selected' : '' ?>>
                                                            Enable</option>
                                                        <option value="0"
                                                            <?= config('setting.use   r_email_verify') == '0' ? 'selected' : '' ?>>
                                                            Disable</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Cookie Consent</label>
                                                    <select class="form-control"
                                                        value="{{ config('setting.cookie_consent') }}"
                                                        name="cookie_consent">
                                                        <option value="1"
                                                            <?= config('setting.cookie_consent') == '1' ? 'selected' : '' ?>>
                                                            Enable</option>
                                                        <option value="0"
                                                            <?= config('setting.cookie_consent') == '0' ? 'selected' : '' ?>>
                                                            Disable</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="navs-top-mail" role="tabpanel">
                                    <form action="{{ route('admin/setting/save') }}" class="ajax-form" method="post">
                                        {{ csrf_field() }}
                                        <div class="form-row row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Host <span class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" class="form-control" placeholder="Host"
                                                            name="host" value="{{ config('mail.mailers.smtp.host') }}"
                                                            required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Encryption</label>
                                                    <select class="form-control"
                                                        value="{{ config('setting.encryption') }}" name="encryption">
                                                        <option value="ssl"
                                                            <?= config('setting.encryption') == 'ssl' ? 'selected' : '' ?>>
                                                            SSL</option>
                                                        <option value="tls"
                                                            <?= config('setting.encryption') == 'tls' ? 'selected' : '' ?>>
                                                            TLS</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Port <span class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" class="form-control" placeholder="Port"
                                                            name="port" value="{{ config('mail.mailers.smtp.port') }}"
                                                            required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Username <span class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" class="form-control" placeholder="Username"
                                                            name="username"
                                                            value="{{ config('mail.mailers.smtp.username') }}" required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Password <span class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" class="form-control" placeholder="Password"
                                                            name="password"
                                                            value="{{ config('mail.mailers.smtp.password') }}" required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Mail From Name <span
                                                            class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" class="form-control"
                                                            placeholder="Mail From Name" name="mail_from_name"
                                                            value="{{ config('setting.mail_from_name') }}" required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Mail From Address <span
                                                            class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" class="form-control"
                                                            placeholder="Mail From Address" name="mail_from_address"
                                                            value="{{ config('setting.mail_from_address') }}" required />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group form-submail">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                    <!-- <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                            data-bs-target="#exampleModal">Email </button> -->
                                                </div>
                                            </div>

                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="navs-top-logo" role="tabpanel">
                                    <div class="row" style="margin-left:0%">
                                        <div class="col-6">
                                            <form action="{{ route('admin/setting/save-logo') }}" class="ajax-file-form"
                                                method="post" enctype="multipart/form-data">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="key" value="logo">
                                                <div class="form-row row">
                                                    <div class="col-md-12">
                                                        <div id="ajax-content">
                                                            <div class="col-sm-6 col-lg-4 mb-4">
                                                                <label class="body">App Logo <span
                                                                        class="star">*</span></label>
                                                                <div class="col-sm-6 col-lg-4 mb-4">
                                                                    <div class="card">
                                                                        <img class="card-img-top preview-app-logo"
                                                                            src="{{ config('setting.app_logo') }}"
                                                                            alt="Card image cap" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <div class="input-group input-group-merge">
                                                                        <input type="file" required="required"
                                                                            name="image"
                                                                            onchange="previewImage(this,'.preview-app-logo')"
                                                                            class="form-control" accept="image/*"
                                                                            id="basic-default-upload-file">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <button type="submit" class="btn btn-primary">Submit</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-6">
                                            <form action="{{ route('admin/setting/save-logo') }}" class="ajax-file-form"
                                                method="post" enctype="multipart/form-data">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="key" value="fevicon">
                                                <div class="form-row row">
                                                    <div class="col-md-12">
                                                        <div id="ajax-content">
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label class="body">App Favicon <span
                                                                            class="star">*</span></label>
                                                                    <div class="col-sm-6 col-lg-4 mb-4">
                                                                        <div class="card">
                                                                            <img class="card-img-top preview-app-fevicon"
                                                                                src="{{ config('setting.app_favicon') }}"
                                                                                alt="Card image cap" />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <div class="input-group input-group-merge">
                                                                        <input type="file" required name="image"
                                                                            onchange="previewImage(this,'.preview-app-fevicon')"
                                                                            class="form-control" accept="image/*"
                                                                            id="input-app-fevicon">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <button type="submit" class="btn btn-primary">Submit</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="navs-top-recaptcha" role="tabpanel">
                                    <form action="{{ route('admin/setting/save') }}" class="ajax-form" method="post">
                                        {{ csrf_field() }}
                                        <div class="form-row row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Enable</label>
                                                    <select class="form-control"
                                                        value="{{ config('setting.google_recaptcha') }}"
                                                        name="google_recaptcha">
                                                        <option value="1"
                                                            <?= config('setting.google_recaptcha') == '1' ? 'selected' : '' ?>>
                                                            Yes</option>
                                                        <option value="0"
                                                            <?= config('setting.google_recaptcha') == '0' ? 'selected' : '' ?>>
                                                            No</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Secret key <span class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" class="form-control" required
                                                            value="{{ config('setting.google_recaptcha_secret_key') }}"
                                                            name="google_recaptcha_secret_key"
                                                            placeholder="google_recaptcha_secret_key">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Public key <span class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" class="form-control" required
                                                            value="{{ config('setting.google_recaptcha_public_key') }}"
                                                            name="google_recaptcha_public_key"
                                                            placeholder="google_recaptcha_public_key">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="navs-top-login" role="tabpanel">
                                    <form action="{{ route('admin/setting/save') }}" class="ajax-form" method="post">
                                        {{ csrf_field() }}
                                        <div class="form-row row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Google Login</label>
                                                    <select class="form-control"
                                                        value="{{ config('setting.google_login') }}" name="google_login">
                                                        <option value="1"
                                                            <?= config('setting.google_login') == '1' ? 'selected' : '' ?>>
                                                            Enable</option>
                                                        <option value="0"
                                                            <?= config('setting.google_login') == '0' ? 'selected' : '' ?>>
                                                            Disable</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Google Client ID <span
                                                            class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" class="form-control"
                                                            value="<?= $setting['services.google_client_id'] ?>"
                                                            name="google.client_id" placeholder="google.client_id">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Google Client Secreat <span
                                                            class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" class="form-control"
                                                            value="<?= $setting['services.google_client_secret'] ?>"
                                                            name="google.client_secret"
                                                            placeholder="google.client_secret">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="navs-top-content" role="tabpanel">
                                    <form action="{{ route('admin/setting/save') }}" class="ajax-form" method="post">
                                        {{ csrf_field() }}
                                        <div class="form-row row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Header</label>
                                                    <div class="input-group input-group-merge">
                                                        <textarea class="form-control" rows="8" name="header_content" placeholder="Header content"><?= $setting['setting.header_content'] ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Footer</label>
                                                    <div class="input-group input-group-merge">
                                                        <textarea class="form-control" rows="8" name="footer_content" placeholder="Footer content"><?= $setting['setting.footer_content'] ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane fade" id="navs-top-payment" role="tabpanel">
                                    <form action="{{ route('admin/setting/save') }}" class="ajax-form" method="post">
                                        {{ csrf_field() }}
                                        <div class="form-row row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Stripe Enable</label>
                                                    <select class="form-control"
                                                        value="{{ config('setting.stripe_enable') }}"
                                                        name="stripe_enable">
                                                        <option value="1"
                                                            {{ config('setting.stripe_enable') == '1' ? 'selected' : '' }}>
                                                            Enable</option>
                                                        <option value="0"
                                                            {{ config('setting.stripe_enable') == '0' ? 'selected' : '' }}>
                                                            Disable</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Stripe Secret Key <span
                                                            class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" class="form-control"
                                                            value= "{{ config('setting.stripe_secret_key') }}"
                                                            name="stripe_secret_key" placeholder="stripe secret key">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="body">Public key <span class="star">*</span></label>
                                                    <div class="input-group input-group-merge">
                                                        <input type="text" class="form-control"
                                                            value="{{ config('setting.stripe_public_key') }}"
                                                            name="stripe_public_key" placeholder="stripe public key">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Mail Process start -->
        <form action="{{ route('admin/setting/save') }}" id="ajax-form" method="POST"
            onsubmit="event.preventDefault()">
            @csrf
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Mail</h5>
                            <button type="button" class="close closebtnmodal" data-bs-dismiss="modal"
                                aria-label="Close">
                                <span aria-hidden="false"><i class="fa-solid fa-xmark"></i></span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="email" class="form-control" id="email" placeholder="Email Address"
                                name="email" aria-label="Name" required />
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- mail Process End -->
        <!-- / Content -->
        <div class="content-backdrop fade"></div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript">
        documentReady(function() {
            $('.ajax-file-form').validate({
                submitHandler: function(form) {
                    app.ajaxFileForm(form);
                }
            })
            $('.ajax-form').validate({
                submitHandler: function(form) {
                    app.ajaxForm(form);
                }
            })
        });
    </script>
@endpush

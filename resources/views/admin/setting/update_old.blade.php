@extends('admin.layouts.main')
@section('title')
    Setting Update
@endsection
@section('content')
    <div class="breadcrumb-box">
        <h4 class="text-xl font-bold text-slate-800">Setting</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="admin/dashboard" class="pjax hover:text-primary-600">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Setting</li>
            </ol>
        </nav>
    </div>
    <div class="">
        <!-- Content -->

        <!-- Tabs -->
        <div class="flex flex-wrap gap-4">
            <div class="w-full">
                <div class="card">
                    <div class="card-header">
                        <h5 class="m-0" style="padding: 21px;">Setting Update</h5>
                        <form action="{{ route('admin/setting/cache-clear') }}"
                            style="padding-top: 25px;padding-right: 15px;">
                            <button type="submit" class="btn-primary">
                                Clear Cache
                            </button>
                        </form>
                    </div>
                    <div>
                        <div class="mb-6" x-data="{ tab: 'navs-top-general' }">
                            <ul class="flex flex-wrap gap-1 border-b border-slate-200" role="tablist">
                                <li class="nav-item">
                                    <button type="button" :class="tab === 'navs-top-general' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px" role="tab" @click="tab = 'navs-top-general'" aria-controls="navs-top-general"
                                        aria-selected="true">
                                        General
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" :class="tab === 'navs-top-mail' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px" role="tab" @click="tab = 'navs-top-mail'" aria-controls="navs-top-mail" aria-selected="false">
                                        Mail
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" :class="tab === 'navs-top-logo' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px" role="tab" @click="tab = 'navs-top-logo'" aria-controls="navs-top-logo" aria-selected="false">
                                        Logo
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" :class="tab === 'navs-top-recaptcha' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px" role="tab" @click="tab = 'navs-top-recaptcha'" aria-controls="navs-top-recaptcha"
                                        aria-selected="false">
                                        Google recaptcha
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" :class="tab === 'navs-top-login' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px" role="tab" @click="tab = 'navs-top-login'" aria-controls="navs-top-login"
                                        aria-selected="false">
                                        Social Login
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" :class="tab === 'navs-top-content' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px" role="tab" @click="tab = 'navs-top-content'" aria-controls="navs-top-content"
                                        aria-selected="false">
                                        Content
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" :class="tab === 'navs-top-payment' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px" role="tab" @click="tab = 'navs-top-payment'" aria-controls="navs-top-payment"
                                        aria-selected="false">
                                        Payment
                                    </button>
                                </li>
                            </ul>
                            <div class="mt-4" id="custom-tabs-one-tabContent">
                                <div x-show="tab === 'navs-top-general'" id="navs-top-general" role="tabpanel"
                                    aria-labelledby="navs-top-general">
                                    <form action="{{ route('admin/setting/save') }}" class="ajax-form" method="post">
                                        {{ csrf_field() }}
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">App Name <span class="text-rose-500">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-input" placeholder="App Name"
                                                            name="app_name" value="{{ config('setting.app_name') }}"
                                                            required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Admin Contact Email <span
                                                            class="text-rose-500">*</span></label>
                                                    <div class="input-group">
                                                        <input type="email" class="form-input"
                                                            placeholder="Admin Contact Email" name="admin_email"
                                                            value="{{ config('setting.admin_email') }}" required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Date Format</label>
                                                    <select class="form-input"
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
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Timezone</label>
                                                    <select class="form-input" value="{{ config('setting.timezone') }}"
                                                        name="timezone">
                                                        @foreach ($timezonelist as $timezone)
                                                            <option value="{{ $timezone }}"
                                                                <?= config('app.timezone') == $timezone ? 'selected' : '' ?>>
                                                                {{ $timezone }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Date Time Format</label>
                                                    <select class="form-input"
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
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Login With OTP</label>
                                                    <select class="form-input"
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
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Email Verify</label>
                                                    <select class="form-input"
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
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Cookie Consent</label>
                                                    <select class="form-input"
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
                                            <div class="md:col-span-2">
                                                <div class="">
                                                    <button type="submit" class="btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div x-show="tab === 'navs-top-mail'" x-cloak id="navs-top-mail" role="tabpanel">
                                    <form action="{{ route('admin/setting/save') }}" class="ajax-form" method="post">
                                        {{ csrf_field() }}
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Host <span class="text-rose-500">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-input" placeholder="Host"
                                                            name="host" value="{{ config('mail.mailers.smtp.host') }}"
                                                            required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Encryption</label>
                                                    <select class="form-input"
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
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Port <span class="text-rose-500">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-input" placeholder="Port"
                                                            name="port" value="{{ config('mail.mailers.smtp.port') }}"
                                                            required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Username <span class="text-rose-500">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-input" placeholder="Username"
                                                            name="username"
                                                            value="{{ config('mail.mailers.smtp.username') }}" required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Password <span class="text-rose-500">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-input" placeholder="Password"
                                                            name="password"
                                                            value="{{ config('mail.mailers.smtp.password') }}" required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Mail From Name <span
                                                            class="text-rose-500">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-input"
                                                            placeholder="Mail From Name" name="mail_from_name"
                                                            value="{{ config('setting.mail_from_name') }}" required />
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Mail From Address <span
                                                            class="text-rose-500">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-input"
                                                            placeholder="Mail From Address" name="mail_from_address"
                                                            value="{{ config('setting.mail_from_address') }}" required />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="md:col-span-2">
                                                <div class="">
                                                    <button type="submit" class="btn-primary">Submit</button>
                                                    <!-- <button type="button" class="btn-primary" data-bs-toggle="modal"
                                                            data-bs-target="#exampleModal">Email </button> -->
                                                </div>
                                            </div>

                                        </div>
                                    </form>
                                </div>
                                <div x-show="tab === 'navs-top-logo'" x-cloak id="navs-top-logo" role="tabpanel">
                                    <div class="flex flex-wrap gap-4" style="margin-left:0%">
                                        <div class="w-1/2">
                                            <form action="{{ route('admin/setting/save-logo') }}" class="ajax-file-form"
                                                method="post" enctype="multipart/form-data">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="key" value="logo">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="md:col-span-2">
                                                        <div id="ajax-content">
                                                            <div class="mb-4">
                                                                <label class="form-label">App Logo <span
                                                                        class="text-rose-500">*</span></label>
                                                                <div class="mb-4">
                                                                    <div class="card">
                                                                        <img class="w-full rounded-t-lg preview-app-logo"
                                                                            src="{{ config('setting.app_logo') }}"
                                                                            alt="Card image cap" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="">
                                                                <div class="mb-3">
                                                                    <div class="input-group">
                                                                        <input type="file" required="required"
                                                                            name="image"
                                                                            onchange="previewImage(this,'.preview-app-logo')"
                                                                            class="form-input" accept="image/*"
                                                                            id="basic-default-upload-file">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <div class="">
                                                            <button type="submit" class="btn-primary">Submit</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="w-1/2">
                                            <form action="{{ route('admin/setting/save-logo') }}" class="ajax-file-form"
                                                method="post" enctype="multipart/form-data">
                                                {{ csrf_field() }}
                                                <input type="hidden" name="key" value="fevicon">
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div class="md:col-span-2">
                                                        <div id="ajax-content">
                                                            <div class="">
                                                                <div class="mb-3">
                                                                    <label class="form-label">App Favicon <span
                                                                            class="text-rose-500">*</span></label>
                                                                    <div class="mb-4">
                                                                        <div class="card">
                                                                            <img class="w-full rounded-t-lg preview-app-fevicon"
                                                                                src="{{ config('setting.app_favicon') }}"
                                                                                alt="Card image cap" />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="">
                                                                <div class="mb-3">
                                                                    <div class="input-group">
                                                                        <input type="file" required name="image"
                                                                            onchange="previewImage(this,'.preview-app-fevicon')"
                                                                            class="form-input" accept="image/*"
                                                                            id="input-app-fevicon">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <div class="">
                                                            <button type="submit" class="btn-primary">Submit</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div x-show="tab === 'navs-top-recaptcha'" x-cloak id="navs-top-recaptcha" role="tabpanel">
                                    <form action="{{ route('admin/setting/save') }}" class="ajax-form" method="post">
                                        {{ csrf_field() }}
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Enable</label>
                                                    <select class="form-input"
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
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Secret key <span class="text-rose-500">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-input" required
                                                            value="{{ config('setting.google_recaptcha_secret_key') }}"
                                                            name="google_recaptcha_secret_key"
                                                            placeholder="google_recaptcha_secret_key">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Public key <span class="text-rose-500">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-input" required
                                                            value="{{ config('setting.google_recaptcha_public_key') }}"
                                                            name="google_recaptcha_public_key"
                                                            placeholder="google_recaptcha_public_key">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="md:col-span-2">
                                                <div class="">
                                                    <button type="submit" class="btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div x-show="tab === 'navs-top-login'" x-cloak id="navs-top-login" role="tabpanel">
                                    <form action="{{ route('admin/setting/save') }}" class="ajax-form" method="post">
                                        {{ csrf_field() }}
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Google Login</label>
                                                    <select class="form-input"
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
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Google Client ID <span
                                                            class="text-rose-500">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-input"
                                                            value="<?= $setting['services.google_client_id'] ?>"
                                                            name="google.client_id" placeholder="google.client_id">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Google Client Secreat <span
                                                            class="text-rose-500">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-input"
                                                            value="<?= $setting['services.google_client_secret'] ?>"
                                                            name="google.client_secret"
                                                            placeholder="google.client_secret">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="md:col-span-2">
                                                <div class="">
                                                    <button type="submit" class="btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div x-show="tab === 'navs-top-content'" x-cloak id="navs-top-content" role="tabpanel">
                                    <form action="{{ route('admin/setting/save') }}" class="ajax-form" method="post">
                                        {{ csrf_field() }}
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Header</label>
                                                    <div class="input-group">
                                                        <textarea class="form-input" rows="8" name="header_content" placeholder="Header content"><?= $setting['setting.header_content'] ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Footer</label>
                                                    <div class="input-group">
                                                        <textarea class="form-input" rows="8" name="footer_content" placeholder="Footer content"><?= $setting['setting.footer_content'] ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="md:col-span-2">
                                                <div class="">
                                                    <button type="submit" class="btn-primary">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div x-show="tab === 'navs-top-payment'" x-cloak id="navs-top-payment" role="tabpanel">
                                    <form action="{{ route('admin/setting/save') }}" class="ajax-form" method="post">
                                        {{ csrf_field() }}
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Stripe Enable</label>
                                                    <select class="form-input"
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
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Stripe Secret Key <span
                                                            class="text-rose-500">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-input"
                                                            value= "{{ config('setting.stripe_secret_key') }}"
                                                            name="stripe_secret_key" placeholder="stripe secret key">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="">
                                                <div class="mb-3">
                                                    <label class="form-label">Public key <span class="text-rose-500">*</span></label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-input"
                                                            value="{{ config('setting.stripe_public_key') }}"
                                                            name="stripe_public_key" placeholder="stripe public key">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="md:col-span-2">
                                                <div class="">
                                                    <button type="submit" class="btn-primary">Submit</button>
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
            <div class="modal fixed inset-0 z-50 hidden items-center justify-center p-4" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                aria-hidden="true">
                <div class="modal-backdrop" data-modal-dismiss></div>
                <div class="modal-dialog relative z-10 w-full max-w-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Mail</h5>
                            <button type="button" class="btn-close closebtnmodal" data-modal-dismiss
                                aria-label="Close">
                                <i class="bx bx-x text-xl"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="email" class="form-input" id="email" placeholder="Email Address"
                                name="email" aria-label="Name" required />
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!-- mail Process End -->
        <!-- / Content -->
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

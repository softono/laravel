@extends('modules.admin.layouts.main')
@section('title', 'Setting Update')
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
<div>
    <!-- Content -->
    <!-- Tabs -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Setting Update</h5>
            <div>
                <button type="submit" class="btn-primary"
                    onclick="app.ajaxGet('{{ route('admin/setting/cache-clear') }}')">
                    Clear Cache
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-6" x-data="{ tab: 'general' }">
                <ul class="flex flex-wrap gap-1 border-b border-slate-200" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors"
                            :class="tab === 'general' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                            @click="tab = 'general'" role="tab" aria-controls="navs-top-general"
                            :aria-selected="tab === 'general'">
                            General
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors"
                            :class="tab === 'logo' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                            @click="tab = 'logo'" role="tab" aria-controls="navs-top-logo"
                            :aria-selected="tab === 'logo'">
                            Logo
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors"
                            :class="tab === 'mail' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                            @click="tab = 'mail'" role="tab" aria-controls="navs-top-mail"
                            :aria-selected="tab === 'mail'">
                            Mail
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors"
                            :class="tab === 'recaptcha' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                            @click="tab = 'recaptcha'" role="tab" aria-controls="navs-top-recaptcha"
                            :aria-selected="tab === 'recaptcha'">
                            Google recaptcha
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors"
                            :class="tab === 'login' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                            @click="tab = 'login'" role="tab" aria-controls="navs-top-login"
                            :aria-selected="tab === 'login'">
                            Social Login
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors"
                            :class="tab === 'content' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
                            @click="tab = 'content'" role="tab" aria-controls="navs-top-content"
                            :aria-selected="tab === 'content'">
                            Content
                        </button>
                    </li>
                </ul>
                <div class="mt-4" id="custom-tabs-one-tabContent">
                    <div x-show="tab === 'general'" id="navs-top-general" role="tabpanel"
                        aria-labelledby="navs-top-general">
                        <form action="{{ route('admin/setting/save') }}" class="ajax-form" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="type" value="general">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">App Name <span
                                                class="text-rose-500">*</span></label>
                                        <div>
                                            <input type="text" class="form-input" placeholder="App Name" id="setting_app_name"
                                                name="setting_app_name" value="{{ $setting['app_name'] }}"
                                                required />
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">Admin Contact Email <span
                                                class="text-rose-500">*</span></label>
                                        <div>
                                            <div class="input-group">
                                                <span class="input-group-text cursor-pointer"><i
                                                        class="bx bx-envelope"></i></span>
                                                <input type="email" id="setting_admin_email" class="form-input"
                                                    placeholder="Admin Contact Email" name="setting_admin_email"
                                                    value="{{ $setting['admin_email'] }}" required />
                                            </div>
                                        </div>
                                        <label id="setting_admin_email-error" class="error text-rose-500" for="setting_admin_email" style="display: none;"></label>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">Date Format</label>
                                        <select class="form-select"
                                            value="{{ $setting['date_format'] }}" name="setting_date_format">
                                            <option value="yyyy-MM-dd"
                                                {{ $setting['date_format'] == 'yyyy-MM-dd' ? 'selected' : '' }}>
                                                {{ date('Y-m-d') }}
                                            </option>
                                            <option value="dd-MM-yyyy"
                                                {{ $setting['date_format'] == 'dd-MM-yyyy' ? 'selected' : '' }}>
                                                {{ date('d-m-Y') }}
                                            </option>
                                            <option value="MM-dd-yyyy"
                                                {{ $setting['date_format'] == 'MM-dd-yyyy' ? 'selected' : '' }}>
                                                {{ date('m-d-Y') }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">Date Time Format</label>

                                        <select class="form-select"
                                            value="{{ $setting['date_time_format'] }}"
                                            name="setting_date_time_format">
                                            <option value="yyyy-MM-dd hh:mm a"
                                                {{ $setting['date_time_format'] == 'yyyy-MM-dd hh:mm a' ? 'selected' : '' }}>
                                                {{ date('Y-m-d h:i A') }}
                                            </option>
                                            <option value="dd-MM-yyyy hh:mm a"
                                                {{ $setting['date_time_format'] == 'dd-MM-yyyy hh:mm a' ? 'selected' : '' }}>
                                                {{ date('d-m-Y h:i A') }}
                                            </option>
                                            <option value="MM-dd-yyyy hh:mm a"
                                                {{ $setting['date_time_format'] == 'MM-dd-yyyy hh:mm a' ? 'selected' : '' }}>
                                                {{ date('m-d-Y h:i A') }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">Login With OTP</label>
                                        <select class="form-select"
                                            value="{{ $setting['user_login_with_otp'] }}"
                                            name="setting_user_login_with_otp">
                                            <option value="1"
                                                {{ $setting['user_login_with_otp'] == '1' ? 'selected' : '' }}>
                                                Enable</option>
                                            <option value="0"
                                                {{ $setting['user_login_with_otp'] == '0' ? 'selected' : '' }}>
                                                Disable</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">Cookie Consent</label>
                                        <select class="form-select"
                                            value="{{ $setting['cookie_consent'] }}"
                                            name="setting_cookie_consent">
                                            <option value="1"
                                                {{ $setting['cookie_consent'] == '1' ? 'selected' : '' }}>
                                                Enable</option>
                                            <option value="0"
                                                {{ $setting['cookie_consent'] == '0' ? 'selected' : '' }}>
                                                Disable</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">Email Verify</label>
                                        <select class="form-select"
                                            value="{{ $setting['user_email_verify'] }}"
                                            name="setting_user_email_verify">
                                            <option value="1"
                                                {{ $setting['user_email_verify'] == '1' ? 'selected' : '' }}>
                                                Enable</option>
                                            <option value="0"
                                                {{ $setting['user_email_verify'] == '0' ? 'selected' : '' }}>
                                                Disable</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <button type="submit" class="btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div x-show="tab === 'logo'" x-cloak id="navs-top-logo" role="tabpanel">
                        <div class="flex flex-wrap gap-4">
                            <div class="w-full md:w-1/2">
                                <form action="{{ route('admin/setting/save-logo') }}" class="ajax-file-form-logo" method="post" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="key" value="app_logo">
                                    <div id="ajax-content">
                                        <div class="mb-4">
                                            <label class="form-label">App Logo <span
                                                    class="text-rose-500">*</span></label>
                                            <div class="mb-4">
                                                <div class="card">
                                                    <img class="w-full rounded-t-lg preview-app-logo"
                                                        src="{{ $general->getFileUrl($setting['app_logo'], 'logo') }}"
                                                        alt="Card image cap" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <input type="file" required="required"
                                                name="image"
                                                onchange="previewImage(this,'.preview-app-logo')"
                                                class="form-input" accept="image/*"
                                                id="applogo">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn-primary">Submit</button>
                                </form>
                            </div>
                            <div class="w-full md:w-[calc(50%-1rem)]">
                                <form action="{{ route('admin/setting/save-logo') }}" class="ajax-file-form-favicon" method="post" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="key" value="app_favicon">
                                    <div id="ajax-content">
                                        <div class="mb-3">
                                            <label class="form-label">App Favicon <span
                                                    class="text-rose-500">*</span></label>
                                            <div class="mb-4">
                                                <div class="card">
                                                    <img class="w-full rounded-t-lg preview-app-fevicon"
                                                        src="{{ $general->getFileUrl($setting['app_favicon'], 'logo') }}"
                                                        alt="Card image cap" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="input-group">
                                                <input type="file" required name="image"
                                                    onchange="previewImage(this,'.preview-app-fevicon')"
                                                    class="form-input" accept="image/*"
                                                    id="input-app-fevicon">
                                            </div>
                                            <label id="input-app-fevicon-error" for="input-app-fevicon" class="error"></label>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn-primary">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div x-show="tab === 'mail'" x-cloak id="navs-top-mail" role="tabpanel">
                        <form action="{{ route('admin/setting/save') }}" class="ajax-form-mail" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="type" value="smtp">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-6">
                                    <div class="mb-3">
                                        <label class="form-label">Host <span
                                                class="text-rose-500">*</span></label>
                                        <div>
                                            <input type="text" class="form-input" id="mail_mailers_smtp_host" placeholder="Host"
                                                name="mail_mailers_smtp_host" value="{{ $setting['smtp_host'] }}"
                                                required />
                                        </div>
                                    </div>
                                </div>
                                <div class="md:col-span-3">
                                    <div class="mb-3">
                                        <label class="form-label">Encryption</label>
                                        <select class="form-select"
                                            value="{{ $setting['smtp_encryption'] }}" name="mail_mailers_smtp_encryption">
                                            <option value="ssl"
                                                {{ $setting['smtp_encryption'] == 'ssl' ? 'selected' : '' }}>
                                                SSL</option>
                                            <option value="tls"
                                                {{ $setting['smtp_encryption'] == 'tls' ? 'selected' : '' }}>
                                                TLS</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="md:col-span-3">
                                    <div class="mb-3">
                                        <label class="form-label">Port <span
                                                class="text-rose-500">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-input" placeholder="Port"
                                                name="mail_mailers_smtp_port" value="{{ $setting['smtp_port'] }}"
                                                required />
                                        </div>
                                    </div>
                                </div>
                                <div class="md:col-span-6">
                                    <div class="mb-3">
                                        <label class="form-label">Username <span
                                                class="text-rose-500">*</span></label>
                                        <div>
                                            <input type="text" class="form-input" placeholder="Username"
                                                id="mail_mailers_smtp_username" name="mail_mailers_smtp_username"
                                                value="{{ $setting['smtp_username'] }}" required />
                                        </div>
                                    </div>
                                </div>
                                <div class="md:col-span-6">
                                    <div class="mb-3">
                                        <label class="form-label">Password <span
                                                class="text-rose-500">*</span></label>
                                        <div>
                                            <input type="text" class="form-input" id="mail_mailers_smtp_password" placeholder="Password"
                                                name="mail_mailers_smtp_password"
                                                value="{{ $setting['smtp_password'] }}" required />
                                        </div>
                                    </div>
                                </div>
                                <div class="md:col-span-6">
                                    <div class="mb-3">
                                        <label class="form-label">Mail From Name <span
                                                class="text-rose-500">*</span></label>
                                        <div>
                                            <input type="text" class="form-input" id="mail_from_name" placeholder="Mail From Name"
                                                name="mail_from_name"
                                                value="{{ $setting['mail_from_name'] }}" required />
                                        </div>

                                    </div>
                                </div>
                                <div class="md:col-span-6">
                                    <div class="mb-3">
                                        <label class="form-label">Mail From Address <span
                                                class="text-rose-500">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i
                                                    class="bx bx-envelope"></i></span>
                                            <input type="text" class="form-input" id="mail_from_address"
                                                placeholder="Mail From Address"
                                                name="mail_from_address"
                                                value="{{ $setting['mail_from_address'] }}" required />
                                        </div>
                                        <label id="mail_from_address-error" class="error" for="mail_from_address" style="display:none;"></label>
                                    </div>
                                </div>

                                <div class="md:col-span-12">
                                    <div class="flex gap-2">
                                        <button type="submit" class="btn-primary">Submit</button>
                                        <button type="button" class="btn-primary" data-modal-open="#email-test">Send Test Email</button>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                    <div x-show="tab === 'recaptcha'" x-cloak id="navs-top-recaptcha" role="tabpanel">

                        <form action="{{ route('admin/setting/save') }}" class="ajax-form-captcha"
                            method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="type" value="captcha">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">Enable</label>
                                        <select class="form-select"
                                            value="{{ $setting['google_recaptcha'] }}"
                                            name="setting_google_recaptcha">
                                            <option value="1"
                                                {{ $setting['google_recaptcha'] == '1' ? 'selected' : '' }}>
                                                Yes</option>
                                            <option value="0"
                                                {{ $setting['google_recaptcha'] == '0' ? 'selected' : '' }}>
                                                No</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">Secret key <span
                                                class="text-rose-500">*</span></label>
                                        <div>
                                            <input type="text" class="form-input" required
                                                value="{{ $setting['google_recaptcha_secret_key'] }}"
                                                name="setting_google_recaptcha_secret_key" id="secret_key"
                                                placeholder="google_recaptcha_secret_key">
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">Public key <span
                                                class="text-rose-500">*</span></label>
                                        <div>
                                            <input type="text" class="form-input" required
                                                value="{{ $setting['google_recaptcha_public_key'] }}"
                                                name="setting_google_recaptcha_public_key" id="public_key"
                                                placeholder="google recaptcha public key">
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <button type="submit" class="btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div x-show="tab === 'login'" x-cloak id="navs-top-login" role="tabpanel">
                        <form action="{{ route('admin/setting/save') }}" class="ajax-form-social"
                            method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="type" value="social">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">Google Login</label>
                                        <select class="form-select"
                                            value="{{ $setting['google_login'] }}"
                                            name="setting_google_login">
                                            <option value="1"
                                                {{ $setting['google_login'] == '1' ? 'selected' : '' }}>
                                                Enable</option>
                                            <option value="0"
                                                {{ $setting['google_login'] == '0' ? 'selected' : '' }}>
                                                Disable</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">Google Client ID <span
                                                class="text-rose-500">*</span></label>
                                        <div>
                                            <input type="text" class="form-input"
                                                value="{{ $setting['google_client_id'] }}" required
                                                name="services_google_client_id"
                                                placeholder="Google client id">
                                        </div>
                                        <label id="client_id-error" class="error" for="client_id"
                                            style="display:none;"></label>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">Google Client Secret <span
                                                class="text-rose-500">*</span></label>
                                        <div>
                                            <input type="text" class="form-input" required
                                                value="{{ $setting['google_client_secret'] }}"
                                                name="services_google_client_secret" placeholder="Google client secret">
                                        </div>
                                        <label id="client_secret-error" class="error" for="client_secret"
                                            style="display:none;"></label>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <button type="submit" class="btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div x-show="tab === 'content'" x-cloak id="navs-top-content" role="tabpanel">
                        <form action="{{ route('admin/setting/save') }}" class="ajax-form-content"
                            method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="type" value="content">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">Header</label>
                                        <textarea class="form-input" rows="8" name="setting_header_content"
                                            placeholder="Header content">{{ $setting['header_content'] }}</textarea>

                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <label class="form-label">Footer</label>
                                        <textarea class="form-input" rows="8" name="setting_footer_content"
                                            placeholder="Footer content">{{ $setting['footer_content'] }}</textarea>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <button type="submit" class="btn-primary">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Mail Process start -->
    <div class="modal fixed inset-0 z-50 hidden items-center justify-center p-4" id="email-test" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-backdrop" data-modal-dismiss></div>
        <div class="modal-dialog relative z-10 w-full max-w-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('admin/setting/mail-process') }}" class="ajax-form-mail-test" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Mail</h5>
                        <button type="button" class="btn-close closebtnmodal" data-modal-dismiss aria-label="Close">
                            <i class="bx bx-x text-xl"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Email <span class="text-rose-500">*</span></label>
                        <input type="email" class="form-input" id="email" placeholder="Email Address" name="email" aria-label="Name" required />
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn-primary">Submit</button>
                        <button type="reset" class="btn-dark btn-reset" data-modal-dismiss aria-label="Close">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- mail Process End -->
</div>
@endsection
@push('scripts')
<script type="text/javascript">
    documentReady(function() {
        $('.ajax-file-form-logo').validate({
            submitHandler: function(form) {
                app.ajaxFileForm(form);
            },
            rules: {
                image: {
                    required: true
                }
            },
            messages: {
                image: {
                    required: "Please enter the app logo"
                }
            }
        });

        $('.ajax-file-form-favicon').validate({
            submitHandler: function(form) {
                app.ajaxFileForm(form);
            },
            rules: {
                image: {
                    required: true
                }
            },
            messages: {
                image: {
                    required: "Please enter the app favicon"
                }
            }
        });

        $('.ajax-form').validate({
            submitHandler: function(form) {
                app.ajaxForm(form);
            },
             rules: {
                setting_app_name: {
                    required: true
                },
                setting_admin_email:{
                    required:true
                }
            },
            messages: {
                setting_app_name: {
                     required: "Please enter the app name"
                },
                setting_admin_email:{
                    required: "Please enter the admin contact email"
                }
            }
        });

        $('.ajax-form-mail').validate({
            submitHandler: function(form) {
                app.ajaxForm(form);
            },
            rules: {
                mail_mailers_smtp_host: {
                    required: true
                },
                mail_mailers_smtp_username: {
                    required: true
                },
                mail_mailers_smtp_password: {
                    required: true
                },
                mail_from_name: {
                    required: true
                },
                mail_from_address: {
                    required: true,
                    email: true
                }
            },
            messages: {
                mail_mailers_smtp_host: {
                    required: "Please enter the mail host"
                },
                mail_mailers_smtp_username: {
                    required: "Please enter the mail username"
                },
                mail_mailers_smtp_password: {
                    required: "Please enter the  password"
                },
                mail_from_name: {
                    required: "Please enter the mail from name"
                },
                mail_from_address: {
                    required: "Please enter the mail from address",
                    email: "Please enter a valid email address"
                }
            }
        });
        $('.ajax-form-mail-test').validate({
            submitHandler: function(form) {
                app.ajaxForm(form);
            },
             rules: {
                email: {
                    required: true,
                    email: true
                }
            },
            messages: {
                email: {
                    required: "Please enter the email address",
                    email: "Enter a valid email address."
            }
        },
        });

        $('.ajax-form-captcha').validate({
            submitHandler: function(form) {
                app.ajaxForm(form);
            },
            rules: {
                setting_google_recaptcha_secret_key: {
                    required: true
                },
                setting_google_recaptcha_public_key: {
                    required:true
                }
            },
            messages: {
                setting_google_recaptcha_secret_key: {
                    required: "Please enter the secret key"
                },
                setting_google_recaptcha_public_key: {
                    required: "Please enter the public key"
                }
            }
        });

        $('.ajax-form-social').validate({
            submitHandler: function(form) {
                app.ajaxForm(form);
            },
             rules: {
                services_google_client_id: {
                    required: true
                },
                services_google_client_secret: {
                    required:true
                }
            },
            messages: {
                services_google_client_id: {
                    required: "Please enter the google client id"
                },
                services_google_client_secret: {
                    required: "Please enter the google client secret"
                }
            }

        });

        $('.ajax-form-content').validate({
            submitHandler: function(form) {
                app.ajaxForm(form);
            },
            rules: {
                setting_header_content: {
                    required: true
                },
                setting_footer_content: {
                    required:true
                }
            },
            messages: {
                setting_header_content: {
                    required: "Please enter the header content"
                },
                setting_footer_content: {
                    required: "Please enter the footer content"
                }
            }
        });

        $('.ajax-form-mail-test').validate({
            submitHandler: function(form) {
                app.ajaxForm(form);
            }
        });
    });
</script>


@endpush
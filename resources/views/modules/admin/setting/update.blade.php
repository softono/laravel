@extends('modules.admin.layouts.main')
@section('title', 'Setting Update')
@section('content')
<x-ui.page-header title="Setting" :crumbs="[['Dashboard', 'admin/dashboard'], ['Setting']]" />
<div>
    <!-- Content -->
    <!-- Tabs -->
    <x-ui.card>
        <x-ui.card-header class="!grid-cols-[1fr_auto] items-center">
            <x-ui.card-title>Setting Update</x-ui.card-title>
            <x-ui.button type="button"
                onclick="app.ajaxPost('{{ route('admin/setting/cache-clear') }}', {}, app.ajaxSuccess)">
                Clear Cache
            </x-ui.button>
        </x-ui.card-header>
        <x-ui.card-content>
            <div class="mb-6" data-tabs data-tabs-active="border-primary text-primary" data-tabs-inactive="border-transparent text-muted-foreground hover:text-foreground">
                <ul class="flex flex-wrap gap-1 border-b border-border" role="tablist">
                    <li>
                        <button type="button" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-primary text-primary"
                            data-tab="general" role="tab" aria-controls="navs-top-general"
                            aria-selected="true">
                            General
                        </button>
                    </li>
                    <li>
                        <button type="button" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-transparent text-muted-foreground hover:text-foreground"
                            data-tab="logo" role="tab" aria-controls="navs-top-logo"
                            aria-selected="false">
                            Logo
                        </button>
                    </li>
                    <li>
                        <button type="button" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-transparent text-muted-foreground hover:text-foreground"
                            data-tab="mail" role="tab" aria-controls="navs-top-mail"
                            aria-selected="false">
                            Mail
                        </button>
                    </li>
                    <li>
                        <button type="button" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-transparent text-muted-foreground hover:text-foreground"
                            data-tab="recaptcha" role="tab" aria-controls="navs-top-recaptcha"
                            aria-selected="false">
                            Google recaptcha
                        </button>
                    </li>
                    <li>
                        <button type="button" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-transparent text-muted-foreground hover:text-foreground"
                            data-tab="login" role="tab" aria-controls="navs-top-login"
                            aria-selected="false">
                            Social Login
                        </button>
                    </li>
                    <li>
                        <button type="button" class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-transparent text-muted-foreground hover:text-foreground"
                            data-tab="content" role="tab" aria-controls="navs-top-content"
                            aria-selected="false">
                            Content
                        </button>
                    </li>
                </ul>
                <div class="mt-4" id="custom-tabs-one-tabContent">
                    <div data-tab-panel="general" id="navs-top-general" role="tabpanel"
                        aria-labelledby="navs-top-general">
                        <form action="{{ route('admin/setting/save') }}" class="ajax-form" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="section" value="general">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">App Name <span
                                                class="text-destructive">*</span></x-ui.label>
                                        <div>
                                            <x-ui.input type="text" placeholder="App Name" id="app_name"
                                                name="app_name" value="{{ $setting['app_name'] }}"
                                                required />
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Admin Contact Email <span
                                                class="text-destructive">*</span></x-ui.label>
                                        <div>
                                            <div class="relative">
<i class="bx bx-envelope text-muted-foreground absolute top-1/2 left-3 -translate-y-1/2"></i>
<x-ui.input class="pl-9" type="email" id="admin_email"
                                                    placeholder="Admin Contact Email" name="admin_email"
                                                    value="{{ $setting['admin_email'] }}" required />
</div>
                                        </div>
                                        <label id="admin_email-error" class="error text-destructive" for="admin_email" style="display: none;"></label>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2" for="default_theme">Default Theme</x-ui.label>
                                        <x-ui.select id="default_theme" name="default_theme">
                                            @foreach (\App\Helpers\ThemeCatalog::all() as $theme)
                                                <option value="{{ $theme['name'] }}"
                                                    {{ ($setting['default_theme'] ?? 'default') == $theme['name'] ? 'selected' : '' }}>
                                                    {{ $theme['label'] }}
                                                </option>
                                            @endforeach
                                        </x-ui.select>
                                        <p class="text-muted-foreground mt-1 text-xs">Used for visitors who have not picked their own theme.</p>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Date Format</x-ui.label>
                                        <x-ui.select
                                            value="{{ $setting['date_format'] }}" name="date_format">
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
                                        </x-ui.select>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Date Time Format</x-ui.label>

                                        <x-ui.select
                                            value="{{ $setting['date_time_format'] }}"
                                            name="date_time_format">
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
                                        </x-ui.select>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Login With OTP</x-ui.label>
                                        <x-ui.select
                                            value="{{ $setting['user_login_with_otp'] }}"
                                            name="user_login_with_otp">
                                            <option value="1"
                                                {{ $setting['user_login_with_otp'] == '1' ? 'selected' : '' }}>
                                                Enable</option>
                                            <option value="0"
                                                {{ $setting['user_login_with_otp'] == '0' ? 'selected' : '' }}>
                                                Disable</option>
                                        </x-ui.select>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Cookie Consent</x-ui.label>
                                        <x-ui.select
                                            value="{{ $setting['cookie_consent'] }}"
                                            name="cookie_consent">
                                            <option value="1"
                                                {{ $setting['cookie_consent'] == '1' ? 'selected' : '' }}>
                                                Enable</option>
                                            <option value="0"
                                                {{ $setting['cookie_consent'] == '0' ? 'selected' : '' }}>
                                                Disable</option>
                                        </x-ui.select>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Email Verify</x-ui.label>
                                        <x-ui.select
                                            value="{{ $setting['user_email_verify'] }}"
                                            name="user_email_verify">
                                            <option value="1"
                                                {{ $setting['user_email_verify'] == '1' ? 'selected' : '' }}>
                                                Enable</option>
                                            <option value="0"
                                                {{ $setting['user_email_verify'] == '0' ? 'selected' : '' }}>
                                                Disable</option>
                                        </x-ui.select>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <x-ui.button type="submit">Submit</x-ui.button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div data-tab-panel="logo" class="hidden" id="navs-top-logo" role="tabpanel">
                        <div class="flex flex-wrap gap-4">
                            <div class="w-full md:w-1/2">
                                <form action="{{ route('admin/setting/save-logo') }}" class="ajax-file-form-logo" method="post" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="key" value="app_logo">
                                    <div id="ajax-content">
                                        <div class="mb-4">
                                            <x-ui.label class="mb-2">App Logo <span
                                                    class="text-destructive">*</span></x-ui.label>
                                            <div class="mb-4">
                                                <div class="overflow-hidden rounded-lg border">
                                                    <img class="w-full preview-app-logo"
                                                        src="{{ $general->getFileUrl($setting['app_logo'], 'logo') }}"
                                                        alt="Card image cap" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <x-ui.input type="file" required="required"
                                                name="image"
                                                onchange="previewImage(this,'.preview-app-logo')"
                                                accept="image/*"
                                                id="applogo" />
                                        </div>
                                    </div>
                                    <x-ui.button type="submit">Submit</x-ui.button>
                                </form>
                            </div>
                            <div class="w-full md:w-[calc(50%-1rem)]">
                                <form action="{{ route('admin/setting/save-logo') }}" class="ajax-file-form-favicon" method="post" enctype="multipart/form-data">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="key" value="app_favicon">
                                    <div id="ajax-content">
                                        <div class="mb-3">
                                            <x-ui.label class="mb-2">App Favicon <span
                                                    class="text-destructive">*</span></x-ui.label>
                                            <div class="mb-4">
                                                <div class="overflow-hidden rounded-lg border">
                                                    <img class="w-full preview-app-fevicon"
                                                        src="{{ $general->getFileUrl($setting['app_favicon'], 'logo') }}"
                                                        alt="Card image cap" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <x-ui.input type="file" required name="image"
                                                    onchange="previewImage(this,'.preview-app-fevicon')"
                                                    accept="image/*"
                                                    id="input-app-fevicon" />
                                            <label id="input-app-fevicon-error" for="input-app-fevicon" class="error"></label>
                                        </div>
                                    </div>
                                    <x-ui.button type="submit">Submit</x-ui.button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div data-tab-panel="mail" class="hidden" id="navs-top-mail" role="tabpanel">
                        <form action="{{ route('admin/setting/save') }}" class="ajax-form-mail" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="section" value="smtp">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-6">
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Host <span
                                                class="text-destructive">*</span></x-ui.label>
                                        <div>
                                            <x-ui.input type="text" id="smtp_host" placeholder="Host"
                                                name="smtp_host" value="{{ $setting['smtp_host'] }}"
                                                required />
                                        </div>
                                    </div>
                                </div>
                                <div class="md:col-span-3">
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Encryption</x-ui.label>
                                        <x-ui.select
                                            value="{{ $setting['smtp_encryption'] }}" name="smtp_encryption">
                                            <option value="ssl"
                                                {{ $setting['smtp_encryption'] == 'ssl' ? 'selected' : '' }}>
                                                SSL</option>
                                            <option value="tls"
                                                {{ $setting['smtp_encryption'] == 'tls' ? 'selected' : '' }}>
                                                TLS</option>
                                        </x-ui.select>
                                    </div>
                                </div>
                                <div class="md:col-span-3">
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Port <span
                                                class="text-destructive">*</span></x-ui.label>
                                        <x-ui.input type="text" placeholder="Port"
                                                name="smtp_port" value="{{ $setting['smtp_port'] }}"
                                                required />
                                    </div>
                                </div>
                                <div class="md:col-span-6">
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Username <span
                                                class="text-destructive">*</span></x-ui.label>
                                        <div>
                                            <x-ui.input type="text" placeholder="Username"
                                                id="smtp_username" name="smtp_username"
                                                value="{{ $setting['smtp_username'] }}" required />
                                        </div>
                                    </div>
                                </div>
                                <div class="md:col-span-6">
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Password <span
                                                class="text-destructive">*</span></x-ui.label>
                                        <div>
                                            <x-ui.input type="text" id="smtp_password" placeholder="Password"
                                                name="smtp_password"
                                                value="{{ $setting['smtp_password'] }}" required />
                                        </div>
                                    </div>
                                </div>
                                <div class="md:col-span-6">
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Mail From Name <span
                                                class="text-destructive">*</span></x-ui.label>
                                        <div>
                                            <x-ui.input type="text" id="mail_from_name" placeholder="Mail From Name"
                                                name="mail_from_name"
                                                value="{{ $setting['mail_from_name'] }}" required />
                                        </div>

                                    </div>
                                </div>
                                <div class="md:col-span-6">
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Mail From Address <span
                                                class="text-destructive">*</span></x-ui.label>
                                        <div class="relative">
<i class="bx bx-envelope text-muted-foreground absolute top-1/2 left-3 -translate-y-1/2"></i>
<x-ui.input class="pl-9" type="text" id="mail_from_address"
                                                placeholder="Mail From Address"
                                                name="mail_from_address"
                                                value="{{ $setting['mail_from_address'] }}" required />
</div>
                                        <label id="mail_from_address-error" class="error" for="mail_from_address" style="display:none;"></label>
                                    </div>
                                </div>

                                <div class="md:col-span-12">
                                    <div class="flex gap-2">
                                        <x-ui.button type="submit">Submit</x-ui.button>
                                        <x-ui.button type="button" data-modal-open="#email-test">Send Test Email</x-ui.button>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                    <div data-tab-panel="recaptcha" class="hidden" id="navs-top-recaptcha" role="tabpanel">

                        <form action="{{ route('admin/setting/save') }}" class="ajax-form-captcha"
                            method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="section" value="captcha">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Enable</x-ui.label>
                                        <x-ui.select
                                            value="{{ $setting['google_recaptcha'] }}"
                                            name="google_recaptcha">
                                            <option value="1"
                                                {{ $setting['google_recaptcha'] == '1' ? 'selected' : '' }}>
                                                Yes</option>
                                            <option value="0"
                                                {{ $setting['google_recaptcha'] == '0' ? 'selected' : '' }}>
                                                No</option>
                                        </x-ui.select>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Secret key <span
                                                class="text-destructive">*</span></x-ui.label>
                                        <div>
                                            <x-ui.input type="text" required
                                                value="{{ $setting['google_recaptcha_secret_key'] }}"
                                                name="google_recaptcha_secret_key" id="secret_key"
                                                placeholder="google_recaptcha_secret_key" />
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Public key <span
                                                class="text-destructive">*</span></x-ui.label>
                                        <div>
                                            <x-ui.input type="text" required
                                                value="{{ $setting['google_recaptcha_public_key'] }}"
                                                name="google_recaptcha_public_key" id="public_key"
                                                placeholder="google recaptcha public key" />
                                        </div>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <x-ui.button type="submit">Submit</x-ui.button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div data-tab-panel="login" class="hidden" id="navs-top-login" role="tabpanel">
                        <form action="{{ route('admin/setting/save') }}" class="ajax-form-social"
                            method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="section" value="social">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Google Login</x-ui.label>
                                        <x-ui.select
                                            value="{{ $setting['google_login'] }}"
                                            name="google_login">
                                            <option value="1"
                                                {{ $setting['google_login'] == '1' ? 'selected' : '' }}>
                                                Enable</option>
                                            <option value="0"
                                                {{ $setting['google_login'] == '0' ? 'selected' : '' }}>
                                                Disable</option>
                                        </x-ui.select>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Google Client ID <span
                                                class="text-destructive">*</span></x-ui.label>
                                        <div>
                                            <x-ui.input type="text"
                                                value="{{ $setting['google_client_id'] }}" required
                                                name="google_client_id"
                                                placeholder="Google client id" />
                                        </div>
                                        <label id="client_id-error" class="error" for="client_id"
                                            style="display:none;"></label>
                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Google Client Secret <span
                                                class="text-destructive">*</span></x-ui.label>
                                        <div>
                                            <x-ui.input type="text" required
                                                value="{{ $setting['google_client_secret'] }}"
                                                name="google_client_secret" placeholder="Google client secret" />
                                        </div>
                                        <label id="client_secret-error" class="error" for="client_secret"
                                            style="display:none;"></label>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <x-ui.button type="submit">Submit</x-ui.button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div data-tab-panel="content" class="hidden" id="navs-top-content" role="tabpanel">
                        <form action="{{ route('admin/setting/save') }}" class="ajax-form-content"
                            method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="section" value="content">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Header</x-ui.label>
                                        <x-ui.textarea rows="8" name="header_content"
                                            placeholder="Header content">{{ $setting['header_content'] }}</x-ui.textarea>

                                    </div>
                                </div>
                                <div>
                                    <div class="mb-3">
                                        <x-ui.label class="mb-2">Footer</x-ui.label>
                                        <x-ui.textarea rows="8" name="footer_content"
                                            placeholder="Footer content">{{ $setting['footer_content'] }}</x-ui.textarea>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <x-ui.button type="submit">Submit</x-ui.button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </x-ui.card-content>
    </x-ui.card>
    <!-- Mail Process start -->
    <x-ui.modal id="email-test">
        <form action="{{ route('admin/setting/mail-process') }}" class="ajax-form-mail-test space-y-4" method="POST">
            @csrf
            <x-ui.dialog-header>
                <x-ui.dialog-title>Mail</x-ui.dialog-title>
            </x-ui.dialog-header>
            <div class="space-y-2">
                <x-ui.label for="email">Email <span class="text-destructive">*</span></x-ui.label>
                <x-ui.input type="email" id="email" placeholder="Email Address" name="email" aria-label="Name" required />
            </div>
            <x-ui.dialog-footer>
                <x-ui.button variant="outline" type="reset" data-modal-dismiss>Cancel</x-ui.button>
                <x-ui.button type="submit">Submit</x-ui.button>
            </x-ui.dialog-footer>
        </form>
    </x-ui.modal>
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
                app_name: {
                    required: true
                },
                admin_email:{
                    required:true
                }
            },
            messages: {
                app_name: {
                     required: "Please enter the app name"
                },
                admin_email:{
                    required: "Please enter the admin contact email"
                }
            }
        });

        $('.ajax-form-mail').validate({
            submitHandler: function(form) {
                app.ajaxForm(form);
            },
            rules: {
                smtp_host: {
                    required: true
                },
                smtp_username: {
                    required: true
                },
                smtp_password: {
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
                smtp_host: {
                    required: "Please enter the mail host"
                },
                smtp_username: {
                    required: "Please enter the mail username"
                },
                smtp_password: {
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
                google_recaptcha_secret_key: {
                    required: true
                },
                google_recaptcha_public_key: {
                    required:true
                }
            },
            messages: {
                google_recaptcha_secret_key: {
                    required: "Please enter the secret key"
                },
                google_recaptcha_public_key: {
                    required: "Please enter the public key"
                }
            }
        });

        $('.ajax-form-social').validate({
            submitHandler: function(form) {
                app.ajaxForm(form);
            },
             rules: {
                google_client_id: {
                    required: true
                },
                google_client_secret: {
                    required:true
                }
            },
            messages: {
                google_client_id: {
                    required: "Please enter the google client id"
                },
                google_client_secret: {
                    required: "Please enter the google client secret"
                }
            }

        });

        $('.ajax-form-content').validate({
            submitHandler: function(form) {
                app.ajaxForm(form);
            },
            rules: {
                header_content: {
                    required: true
                },
                footer_content: {
                    required:true
                }
            },
            messages: {
                header_content: {
                    required: "Please enter the header content"
                },
                footer_content: {
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
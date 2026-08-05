<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DataSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SEED_ADMIN_PASSWORD', 'ChangeMe123!');
        $hashedPassword = Hash::make($password);

        // -------------------------------------------------------------------
        // 1. Users & User Accounts
        // -------------------------------------------------------------------
        $usersData = [
            [
                'id' => '08a176840ef75f6eb6809ec011c75042',
                'role' => 'SUPER_ADMIN',
                'first_name' => 'Admin',
                'last_name' => 'Super',
                'email' => 'jackdeveloper100+admin@gmail.com',
                'phone' => '9898989898',
                'two_factor_enabled' => false,
                'status' => 'active',
                'email_verified' => true,
                'country' => 'IN',
                'timezone' => 'Asia/Calcutta',
                'registered_ip' => '2401:4900:8898:bd6f:4595:a448:33a7:1d6b',
                'permission' => null,
                'created_at' => '2025-06-19 10:14:40',
                'updated_at' => '2026-02-18 09:38:28',
            ],
            [
                'id' => '3b90f42b3eb64d7fa311f930e428cf11',
                'role' => 'ADMIN',
                'first_name' => 'jack',
                'last_name' => 'developer',
                'email' => 'jackdeveloper100+subadmin@gmail.com',
                'phone' => '9898989891',
                'timezone' => 'Asia/Calcutta',
                'two_factor_enabled' => false,
                'email_verified' => true,
                'status' => 'active',
                'country' => 'IN',
                'permission' => 'admin_admin,admin/admin,admin/admin/view,admin/admin/create,admin/admin/update,admin/admin/delete,admin_user,admin/user,admin/user/view,admin/user/create,admin/user/update,admin/user/delete,admin_bucket,admin/bucket,admin/bucket/view,admin_setting,admin/setting/update,admin_activity,admin/activity,admin_email_template,admin/email_template,admin/email_template/view,admin/email_template/update,admin_device,admin/device,admin/device/logout',
                'registered_ip' => '2401:4900:8899:7daf:6d39:63cb:35be:ed97',
                'created_at' => '2025-12-22 11:42:46',
                'updated_at' => '2026-06-16 05:00:57',
            ],
            [
                'id' => 'f81c9b689e4722ad9f485121ca039b20',
                'role' => 'USER',
                'first_name' => 'Jack',
                'last_name' => 'developer',
                'email' => 'jackdeveloper100@gmail.com',
                'phone' => '9898989892',
                'registered_ip' => '2401:4900:8898:bd6f:4595:a448:33a7:1d6b',
                'two_factor_enabled' => false,
                'email_verified' => true,
                'status' => 'active',
                'country' => 'IN',
                'timezone' => 'Asia/Calcutta',
                'permission' => null,
                'created_at' => '2025-12-23 09:29:57',
                'updated_at' => '2026-06-15 09:23:53',
            ],
        ];

        foreach ($usersData as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );

            DB::table('user_accounts')->updateOrInsert(
                ['user_id' => $user['id'], 'provider_id' => 'credential'],
                [
                    'id' => (string) Str::uuid(),
                    'account_id' => $user['id'],
                    'provider_id' => 'credential',
                    'user_id' => $user['id'],
                    'password' => $hashedPassword,
                    'created_at' => $user['created_at'],
                    'updated_at' => $user['updated_at'],
                ]
            );

            // Legacy user table seeding if exists
            if (DB::getSchemaBuilder()->hasTable('user')) {
                $roleInt = match ($user['role']) {
                    'SUPER_ADMIN' => 0,
                    'ADMIN' => 1,
                    default => 4,
                };
                DB::table('user')->updateOrInsert(
                    ['email' => $user['email']],
                    [
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'email' => $user['email'],
                        'phone' => $user['phone'],
                        'password' => $hashedPassword,
                        'email_verified' => 1,
                        'role' => $roleInt,
                        'status' => 1,
                        'country' => $user['country'],
                        'timezone' => $user['timezone'],
                        'permission' => $user['permission'],
                        'registered_ip' => $user['registered_ip'],
                        'created_at' => $user['created_at'],
                        'updated_at' => $user['updated_at'],
                    ]
                );
            }
        }
        $this->command?->info('Seeded users and user_accounts.');

        // -------------------------------------------------------------------
        // 2. Settings
        // -------------------------------------------------------------------
        $settingsData = [
            ['key' => 'google_recaptcha', 'value' => '1', 'type' => 1],
            ['key' => 'google_recaptcha_secret_key', 'value' => 'secret_key', 'type' => 0],
            ['key' => 'google_recaptcha_public_key', 'value' => 'public_key', 'type' => 1],
            ['key' => 'smtp_host', 'value' => 'smtp.gmail.com', 'type' => 0],
            ['key' => 'smtp_port', 'value' => '465', 'type' => 0],
            ['key' => 'smtp_encryption', 'value' => 'ssl', 'type' => 0],
            ['key' => 'smtp_username', 'value' => 'mailstack365@gmail.com', 'type' => 0],
            ['key' => 'smtp_password', 'value' => 'secret_key', 'type' => 0],
            ['key' => 'mail_from_address', 'value' => 'mailstack365@gmail.com', 'type' => 0],
            ['key' => 'mail_from_name', 'value' => 'Next App', 'type' => 0],
            ['key' => 'user_email_verify', 'value' => '1', 'type' => 1],
            ['key' => 'user_login_with_otp', 'value' => '0', 'type' => 1],
            ['key' => 'admin_email', 'value' => 'jack@yopmail.com', 'type' => 1],
            ['key' => 'password_type', 'value' => '1', 'type' => 0],
            ['key' => 'app_base_url', 'value' => 'https://app.tribital.com', 'type' => 1],
            ['key' => 'date_format', 'value' => 'dd-MM-yyyy', 'type' => 1],
            ['key' => 'date_time_format', 'value' => 'yyyy-MM-dd hh:mm a', 'type' => 1],
            ['key' => 'google_client_id', 'value' => 'public_key', 'type' => 1],
            ['key' => 'google_client_secret', 'value' => 'secret_key', 'type' => 0],
            ['key' => 'google_login', 'value' => '1', 'type' => 0],
            ['key' => 'cookie_consent', 'value' => '1', 'type' => 1],
            ['key' => 'header_content', 'value' => '', 'type' => 1],
            ['key' => 'footer_content', 'value' => '', 'type' => 1],

            // The rows above are legacy/unprefixed and don't match any key
            // the admin/setting/update Blade actually reads ($setting['setting.xxx'])
            // - none of them, nor an admin Settings save, ever populated
            // these, so every tab 500'd on first load. Seeded here (with
            // the "setting."/"mail."/"services." prefix SettingRepository::store()
            // writes) so the page renders and edits persist via updateOne().
            ['key' => 'setting.app_name', 'value' => 'Storage Server', 'type' => 0],
            ['key' => 'setting.admin_email', 'value' => env('SEED_ADMIN_EMAIL', 'admin@example.com'), 'type' => 0],
            ['key' => 'setting.date_format', 'value' => 'Y-m-d', 'type' => 0],
            ['key' => 'setting.date_time_format', 'value' => 'Y-m-d h:i A', 'type' => 0],
            ['key' => 'setting.user_login_with_otp', 'value' => '0', 'type' => 0],
            ['key' => 'setting.cookie_consent', 'value' => '1', 'type' => 0],
            ['key' => 'setting.user_email_verify', 'value' => '1', 'type' => 0],
            ['key' => 'setting.app_logo', 'value' => 'logo.png', 'type' => 0],
            ['key' => 'setting.app_favicon', 'value' => 'favicon.png', 'type' => 0],
            ['key' => 'mail.mailers.smtp.host', 'value' => env('MAIL_HOST', ''), 'type' => 0],
            ['key' => 'mail.mailers.smtp.port', 'value' => (string) env('MAIL_PORT', 587), 'type' => 0],
            ['key' => 'mail.mailers.smtp.encryption', 'value' => env('MAIL_ENCRYPTION', 'tls'), 'type' => 0],
            ['key' => 'mail.mailers.smtp.username', 'value' => env('MAIL_USERNAME', ''), 'type' => 0],
            ['key' => 'mail.mailers.smtp.password', 'value' => env('MAIL_PASSWORD', ''), 'type' => 0],
            ['key' => 'mail.from.address', 'value' => env('MAIL_FROM_ADDRESS', ''), 'type' => 0],
            ['key' => 'mail.from.name', 'value' => env('APP_NAME', 'Storage Server'), 'type' => 0],
            ['key' => 'setting.google_recaptcha', 'value' => '0', 'type' => 0],
            ['key' => 'setting.google_recaptcha_secret_key', 'value' => '', 'type' => 0],
            ['key' => 'setting.google_recaptcha_public_key', 'value' => '', 'type' => 0],
            ['key' => 'services.google_client_id', 'value' => env('GOOGLE_CLIENT_ID', ''), 'type' => 0],
            ['key' => 'services.google_client_secret', 'value' => env('GOOGLE_CLIENT_SECRET', ''), 'type' => 0],
            ['key' => 'setting.google_login', 'value' => '0', 'type' => 0],
            ['key' => 'setting.header_content', 'value' => '', 'type' => 0],
            ['key' => 'setting.footer_content', 'value' => '', 'type' => 0],

            // Storage Engine settings (see admin/setting/update "Storage"
            // tab and config/setting.php for the fallback defaults these
            // override). Keys are stored WITH the "setting." prefix here,
            // unlike the legacy entries above - SettingRepository::store()
            // always writes prefixed keys, and SettingRepository::updateOne()
            // only ever updates an existing row, never creates one, so
            // these must be pre-seeded for the admin Settings form to work.
            ['key' => 'setting.storage_path', 'value' => 'buckets', 'type' => 0],
            ['key' => 'setting.storage_max_upload_size', 'value' => (string) (5 * 1024 * 1024 * 1024), 'type' => 0],
            ['key' => 'setting.storage_allowed_file_types', 'value' => '*', 'type' => 0],
            ['key' => 'setting.storage_default_visibility', 'value' => 'private', 'type' => 0],
            ['key' => 'setting.storage_api_endpoint', 'value' => env('APP_URL', ''), 'type' => 0],
            ['key' => 'setting.storage_cors_allowed_origins', 'value' => '*', 'type' => 0],
            ['key' => 'setting.storage_rate_limit_per_minute', 'value' => '60', 'type' => 0],
        ];

        if (DB::getSchemaBuilder()->hasTable('settings')) {
            foreach ($settingsData as $s) {
                DB::table('settings')->updateOrInsert(
                    ['key' => $s['key']],
                    ['key' => $s['key'], 'value' => $s['value'], 'type' => $s['type']]
                );
            }
        }
        $this->command?->info('Seeded setting entries.');

        // -------------------------------------------------------------------
        // 3. Email Templates
        // -------------------------------------------------------------------
        $emailTemplatesData = [
            [
                'key' => 'login-link',
                'title' => 'Login Link',
                'subject' => 'Your login link for {{message}} | {{app_name}}',
                'body' => '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableDescription" style="color: rgb(0, 0, 0); font-family: &quot;Times New Roman&quot;; font-size: medium;"><tbody><tr><td align="center" valign="top" class="description" style="padding-bottom: 20px;"><h2 class="text-h2" style="color:#000000;font-weight:500; font-family: Poppins, Helvetica, Arial, sans-serif; font-size: 28px; line-height: 36px; padding: 0px; margin-right: 0px; margin-bottom: 5px; margin-left: 0px;margin-top:0;">&nbsp;Hello, {{first_name}} {{last_name}}!</h2><h4 class="text" style="font-size: 16px; color: rgb(153, 153, 153); font-family: Poppins, Helvetica, Arial, sans-serif; line-height: 24px;font-weight:500; padding: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px;margin-top: 0px;">Click the button below to continue your {{message}}.</h4><p style="margin-top: 24px;"><a href="{{link}}" style="display:inline-block; background:#1f2937; color:#ffffff; text-decoration:none; font-family: Poppins, Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 600; padding: 14px 28px; border-radius: 8px;">Login link</a></p></td></tr></tbody></table>',
                'params' => 'first_name,last_name,link,message',
                'created_at' => '2026-07-01 00:00:00',
                'updated_at' => '2026-07-01 00:00:00',
            ],
            [
                'key' => 'otp',
                'title' => 'OTP',
                'subject' => 'OTP for {{message}} | {{app_name}}',
                'body' => '<h6 class=""><table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableDescription" style="color: rgb(0, 0, 0); font-family: &quot;Times New Roman&quot;; font-size: medium;"><tbody><tr><td align="center" valign="top" class="description" style="padding-bottom: 20px;"><h2 class="text-h2" style="color:#000000;font-weight:500; font-family: Poppins, Helvetica, Arial, sans-serif; font-size: 28px; line-height: 36px; padding: 0px; margin-right: 0px; margin-bottom: 5px; margin-left: 0px;margin-top:0;">&nbsp;Hello, {{first_name}} {{last_name}}!</h2><p style="color: rgb(0, 0, 0); font-weight: 500; font-family: Poppins, Helvetica, Arial, sans-serif; font-size: 28px; line-height: 36px; padding: 0px; margin: 0px 0px 5px;"><br></p><h4 class="text" style="font-size: 16px; color: rgb(153, 153, 153); font-family: Poppins, Helvetica, Arial, sans-serif; line-height: 24px;font-weight:500; padding: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px;margin-top: 0px;">Here is your OTP for {{message}}</h4><h3 style="margin-top: 18px;"><span style="display:inline-block; font-family: Poppins, Helvetica, Arial, sans-serif; font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #1f2937; background: #eaf6f2; padding: 12px 20px; border-radius: 999px;">{{otp}}</span></h3></td></tr></tbody></table></h6>',
                'params' => 'first_name,last_name,otp,message',
                'created_at' => '2025-12-19 10:20:41',
                'updated_at' => '2026-06-18 13:24:11',
            ],
            [
                'key' => 'welcome',
                'title' => 'Welcome',
                'subject' => 'Welcome {{first_name}} to {{app_name}}!',
                'body' => '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableDescription" style="color: rgb(0, 0, 0); font-family: &quot;Times New Roman&quot;; font-size: medium;"><tbody><tr><td align="center" valign="top" class="description" style="padding-bottom: 20px;"><h2 class="text-h2" style="color:#000000;font-weight:500; font-family: Poppins, Helvetica, Arial, sans-serif; font-size: 28px; line-height: 36px; padding: 0px; margin-right: 0px; margin-bottom: 5px; margin-left: 0px;margin-top:0;">&nbsp;Hello,</h2><h4 class="text" style="font-size: 16px; color: rgb(153, 153, 153); font-family: Poppins, Helvetica, Arial, sans-serif; line-height: 24px;font-weight:500; padding: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px;margin-top: 0px;">Welcome to {{app_name}}</h4><p style="font-family:\'Helvetica\',sans-serif;font-size:14px;font-weight:500;"><font color="#666666">Thank You!</font></p></td></tr></tbody></table>',
                'params' => 'first_name,last_name',
                'created_at' => '2026-01-07 11:36:05',
                'updated_at' => '2026-06-18 08:33:45',
            ],
        ];

        if (DB::getSchemaBuilder()->hasTable('email_templates')) {
            foreach ($emailTemplatesData as $tpl) {
                DB::table('email_templates')->updateOrInsert(
                    ['key' => $tpl['key']],
                    $tpl
                );
            }
        }
        $this->command?->info('Seeded email templates.');

    }
}

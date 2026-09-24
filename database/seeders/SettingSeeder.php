<?php

namespace Database\Seeders;

use App\Repositories\SettingRepository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The `settings` rows from Next's db/seed/settings.sql. Non-destructive:
 * existing keys keep their value, only missing ones are inserted, so it can
 * be re-run on a live database (`php artisan db:seed --class=SettingSeeder`).
 * The three secret keys are seeded empty; set real values with `settings:set`,
 * which encrypts them.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'google_recaptcha_secret_key', 'value' => '', 'type' => 'private', 'group' => 'captcha'],
            ['key' => 'smtp_password', 'value' => '', 'type' => 'private', 'group' => null],
            ['key' => 'google_client_secret', 'value' => '', 'type' => 'private', 'group' => 'social'],
            ['key' => 'google_recaptcha', 'value' => '1', 'type' => 'public', 'group' => 'captcha'],
            ['key' => 'google_recaptcha_public_key', 'value' => '6LcqacApAAAAAOeMK7u0jdBV07mc2jPo7EUZwCg9', 'type' => 'public', 'group' => 'captcha'],
            ['key' => 'smtp_host', 'value' => 'smtp.gmail.com', 'type' => 'private', 'group' => null],
            ['key' => 'smtp_port', 'value' => '465', 'type' => 'private', 'group' => null],
            ['key' => 'smtp_encryption', 'value' => 'ssl', 'type' => 'private', 'group' => null],
            ['key' => 'smtp_username', 'value' => 'mailstack365@gmail.com', 'type' => 'private', 'group' => null],
            ['key' => 'mail_from_address', 'value' => 'mailstack365@gmail.com', 'type' => 'private', 'group' => null],
            ['key' => 'mail_from_name', 'value' => 'Next App', 'type' => 'private', 'group' => null],
            ['key' => 'user_email_verify', 'value' => '1', 'type' => 'public', 'group' => null],
            ['key' => 'user_login_with_otp', 'value' => '0', 'type' => 'public', 'group' => null],
            ['key' => 'admin_email', 'value' => 'himanshuaier@gmail.com', 'type' => 'public', 'group' => null],
            ['key' => 'password_type', 'value' => '1', 'type' => 'private', 'group' => null],
            ['key' => 'app_base_url', 'value' => 'https://app.tribital.com', 'type' => 'public', 'group' => null],
            ['key' => 'date_format', 'value' => 'dd-MM-yyyy', 'type' => 'public', 'group' => null],
            ['key' => 'date_time_format', 'value' => 'yyyy-MM-dd hh:mm a', 'type' => 'public', 'group' => null],
            ['key' => 'google_client_id', 'value' => '4360060983-s6fei9gsrji2979pddsrn8o7bo8ec7cp.apps.googleusercontent.com', 'type' => 'public', 'group' => 'social'],
            ['key' => 'google_login', 'value' => '1', 'type' => 'private', 'group' => 'social'],
            ['key' => 'cookie_consent', 'value' => '1', 'type' => 'public', 'group' => null],
            ['key' => 'header_content', 'value' => '', 'type' => 'public', 'group' => 'content'],
            ['key' => 'footer_content', 'value' => '', 'type' => 'public', 'group' => 'content'],
        ];

        $existing = DB::table('settings')->pluck('key')->all();

        foreach ($settings as $setting) {
            if (! in_array($setting['key'], $existing, true)) {
                DB::table('settings')->insert($setting);
            }
        }

        $this->command?->info('Seeded setting entries.');
        app(SettingRepository::class)->clearCache();
    }
}

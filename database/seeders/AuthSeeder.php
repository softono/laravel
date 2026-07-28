<?php

namespace Database\Seeders;

use App\Constants\UserRole;
use App\Constants\UserStatus;
use App\Models\Auth\User;
use App\Models\Auth\UserAccount;
use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a single SUPER_ADMIN account into the new (fresh-start) auth
 * tables, so the admin panel is reachable right after migrating.
 *
 * Run explicitly (not wired into DatabaseSeeder, which still seeds the
 * legacy `user` table via a pre-existing, unrelated factory call):
 *
 *   php artisan db:seed --class=Database\\Seeders\\AuthSeeder
 */
class AuthSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLoginLinkEmailTemplate();

        $email = env('SEED_ADMIN_EMAIL', 'admin@example.com');
        $password = env('SEED_ADMIN_PASSWORD', 'ChangeMe123!');

        if (User::where('email', $email)->exists()) {
            $this->command?->info("AuthSeeder: a user with email {$email} already exists, skipping.");

            return;
        }

        $user = User::create([
            'email' => $email,
            'email_verified' => true,
            'role' => UserRole::SUPER_ADMIN,
            'status' => UserStatus::ACTIVE,
            'first_name' => env('SEED_ADMIN_FIRST_NAME', 'Super'),
            'last_name' => env('SEED_ADMIN_LAST_NAME', 'Admin'),
            'timezone' => 'UTC',
        ]);

        UserAccount::create([
            'user_id' => $user->id,
            'account_id' => $user->id,
            'provider_id' => 'credential',
            'password' => Hash::make($password),
        ]);

        $this->command?->info("AuthSeeder: created SUPER_ADMIN {$email}.");

        if (! env('SEED_ADMIN_PASSWORD')) {
            $this->command?->warn('AuthSeeder: SEED_ADMIN_PASSWORD not set in .env - used the default "ChangeMe123!". Change it immediately after first login.');
        }
    }

    /**
     * The magic-login-link flow is dead without this template (R-10 in
     * the plan) - the existing email_template table has no row for it.
     */
    protected function seedLoginLinkEmailTemplate(): void
    {
        if (EmailTemplate::where('key', 'login-link')->exists()) {
            return;
        }

        EmailTemplate::create([
            'key' => 'login-link',
            'title' => 'Magic Login Link',
            'subject' => 'Your login link for {{app_name}}',
            'body' => '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableDescription" style="color: rgb(0, 0, 0); font-family: Poppins, Helvetica, Arial, sans-serif;">'
                .'<tbody><tr><td align="center" valign="top" class="description" style="padding-bottom: 20px;">'
                .'<h2 style="color:#000000;font-weight:500;font-size:28px;line-height:36px;margin:0 0 5px;">Hello, {{first_name}} {{last_name}}!</h2>'
                .'<h4 style="font-size:16px;color:#999999;font-weight:500;line-height:24px;margin:0;">Click the button below to sign in on your other device.</h4>'
                .'<p style="margin:24px 0;"><a href="{{link}}" style="background:#6966FF;color:#ffffff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:600;">Log In</a></p>'
                .'<p style="font-size:14px;color:#666666;">Confirm this code matches what you see on the other device: <strong style="font-size:20px;letter-spacing:2px;">{{code}}</strong></p>'
                .'<p style="font-size:12px;color:#999999;">This link expires in 5 minutes. If you did not request this, ignore this email.</p>'
                .'</td></tr></tbody></table>',
            'params' => 'first_name,last_name,link,code',
        ]);

        $this->command?->info('AuthSeeder: created the login-link email template.');
    }
}

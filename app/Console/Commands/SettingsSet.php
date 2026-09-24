<?php

namespace App\Console\Commands;

use App\Repositories\SettingRepository;
use Illuminate\Console\Command;

/**
 * Writes one `settings` row from the command line, so secrets (SMTP password,
 * OAuth and reCAPTCHA keys) never have to be typed into the admin form or
 * shared shell history. Secret keys are encrypted by SettingRepository; only
 * a masked value is echoed back.
 */
class SettingsSet extends Command
{
    protected $description = 'Set a single application setting (secrets are encrypted at rest)';

    protected $signature = 'settings:set {key : Setting key, e.g. smtp_password} {value? : New value (prompted, hidden, when omitted)}';

    public function handle(SettingRepository $settings): int
    {
        $key = (string) $this->argument('key');
        $value = $this->argument('value') ?? $this->secret("Value for {$key}");

        $settings->setMany([$key => (string) $value]);

        $this->info("Set \"{$key}\" - stored value: ".$this->mask($settings->all()[$key] ?? ''));

        return self::SUCCESS;
    }

    private function mask(string $value): string
    {
        if ($value === '') {
            return '(empty)';
        }

        return strlen($value) <= 4 ? str_repeat('*', strlen($value)) : str_repeat('*', strlen($value) - 4).substr($value, -4);
    }
}

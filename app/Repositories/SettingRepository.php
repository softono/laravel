<?php

namespace App\Repositories;

use App\Helpers\Encryption;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * `settings` rows use the same plain keys as the Next app (`smtp_host`,
 * `user_email_verify`, ...). The SECRET_KEYS values are encrypted at rest
 * (see Encryption) and decrypted on read.
 */
class SettingRepository
{
    private const CACHE_KEY = 'setting';

    /** Encrypted in the `value` column, like Next's ENCRYPTED_SETTING_KEYS. */
    private const SECRET_KEYS = ['google_client_secret', 'smtp_password', 'google_recaptcha_secret_key'];

    /** Setting keys that override a non-`setting.*` Laravel config key. */
    private const CONFIG_KEYS = [
        'smtp_host' => 'mail.mailers.smtp.host',
        'smtp_port' => 'mail.mailers.smtp.port',
        'smtp_username' => 'mail.mailers.smtp.username',
        'smtp_password' => 'mail.mailers.smtp.password',
        'smtp_encryption' => 'mail.mailers.smtp.encryption',
        'mail_from_address' => 'mail.from.address',
        'mail_from_name' => 'mail.from.name',
        'google_client_id' => 'services.google.client_id',
        'google_client_secret' => 'services.google.client_secret',
    ];

    /**
     * Decrypted key => value map. The cache holds the raw rows, so secrets are
     * never stored in plaintext in the cache table.
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        $rows = Cache::remember(self::CACHE_KEY, now()->addDay(), fn () => Setting::pluck('value', 'key')->all());

        foreach (self::SECRET_KEYS as $key) {
            if (isset($rows[$key])) {
                $rows[$key] = Encryption::decrypt($rows[$key]);
            }
        }

        return $rows;
    }

    /**
     * Creates or updates each key. Existing rows keep their type and group.
     *
     * @param  array<string, string|null>  $values
     */
    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            $value ??= '';
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => in_array($key, self::SECRET_KEYS, true) ? Encryption::encrypt($value) : $value],
            );
        }

        $this->clearCache();
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Rows keep Next's date-fns patterns (`dd-MM-yyyy hh:mm a`); PHP's date() needs its own.
     */
    public static function toPhpDateFormat(string $pattern): string
    {
        return strtr($pattern, [
            'yyyy' => 'Y', 'MM' => 'm', 'dd' => 'd', 'HH' => 'H', 'hh' => 'h', 'mm' => 'i', 'ss' => 's', 'a' => 'A',
        ]);
    }

    /**
     * Laravel config entries derived from the settings rows.
     *
     * @return array<string, string>
     */
    public function configOverrides(): array
    {
        $config = [];
        foreach ($this->all() as $key => $value) {
            if ($key === 'date_format' || $key === 'date_time_format') {
                $value = self::toPhpDateFormat($value);
            }
            $config[self::CONFIG_KEYS[$key] ?? 'setting.'.$key] = $value;
        }

        return $config;
    }
}

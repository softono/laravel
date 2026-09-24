<?php

namespace App\Helpers;

use RuntimeException;

/**
 * AES-256-GCM with a SHA-256 derived key from ENCRYPTION_KEY. The payload is
 * base64(iv[12] . tag[16] . ciphertext), the same layout Next's
 * `lib/encryption.ts` writes, so both apps can read each other's values.
 */
class Encryption
{
    private const CIPHER = 'aes-256-gcm';

    private const IV_LENGTH = 12;

    private const TAG_LENGTH = 16;

    public static function encrypt(string $plaintext): string
    {
        if ($plaintext === '') {
            return $plaintext;
        }

        $iv = random_bytes(self::IV_LENGTH);
        $encrypted = openssl_encrypt($plaintext, self::CIPHER, self::key(), OPENSSL_RAW_DATA, $iv, $tag, '', self::TAG_LENGTH);

        if ($encrypted === false) {
            throw new RuntimeException('Encryption failed');
        }

        return base64_encode($iv.$tag.$encrypted);
    }

    public static function decrypt(string $ciphertext): string
    {
        if ($ciphertext === '') {
            return $ciphertext;
        }

        $buffer = base64_decode($ciphertext, true);

        if ($buffer === false || strlen($buffer) < self::IV_LENGTH + self::TAG_LENGTH) {
            throw new RuntimeException('Ciphertext too short to be valid');
        }

        $iv = substr($buffer, 0, self::IV_LENGTH);
        $tag = substr($buffer, self::IV_LENGTH, self::TAG_LENGTH);
        $plaintext = openssl_decrypt(substr($buffer, self::IV_LENGTH + self::TAG_LENGTH), self::CIPHER, self::key(), OPENSSL_RAW_DATA, $iv, $tag);

        if ($plaintext === false) {
            throw new RuntimeException('Decryption failed');
        }

        return $plaintext;
    }

    private static function key(): string
    {
        $key = (string) config('auth_next.encryption_key');

        if ($key === '') {
            throw new RuntimeException('ENCRYPTION_KEY is not set');
        }

        return hash('sha256', $key, true);
    }
}

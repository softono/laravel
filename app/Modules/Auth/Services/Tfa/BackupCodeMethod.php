<?php

namespace App\Modules\Auth\Services\Tfa;

use Illuminate\Support\Facades\Hash;

/**
 * Backup codes: 10 codes of strtoupper(bin2hex(random_bytes(4))) (8 hex
 * chars), each argon2id-hashed and stored as a JSON array in
 * user_two_factors.backup_codes. A used code is spliced out - single use.
 */
class BackupCodeMethod
{
    /** @return string[] plaintext codes - shown to the user exactly once */
    public function generate(): array
    {
        $count = (int) config('auth_next.backup_code_count');

        return array_map(
            fn () => strtoupper(bin2hex(random_bytes(4))),
            range(1, $count),
        );
    }

    /** @param string[] $codes @return string[] hashed, to store as JSON */
    public function hash(array $codes): array
    {
        return array_map(fn ($code) => Hash::make($code), $codes);
    }

    /**
     * @param  string  $hashedJson  JSON array of hashed codes
     * @return array{valid: bool, remaining: ?string} remaining is the updated JSON to persist
     */
    public function verify(string $hashedJson, string $code): array
    {
        $hashes = json_decode($hashedJson, true) ?: [];
        $code = strtoupper(trim($code));

        foreach ($hashes as $i => $hash) {
            if (Hash::check($code, $hash)) {
                unset($hashes[$i]);

                return ['valid' => true, 'remaining' => json_encode(array_values($hashes))];
            }
        }

        return ['valid' => false, 'remaining' => null];
    }
}

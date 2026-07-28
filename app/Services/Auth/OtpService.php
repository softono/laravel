<?php

namespace App\Services\Auth;

use App\Models\Auth\UserVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Port of the Next app's src/server/modules/auth/otp.service.ts.
 *
 * identifier = "{purpose}:{lowercased email}". The OTP itself is a 6-digit
 * code, argon2id-hashed into `value`. Prior rows for the same identifier
 * are deleted before issuing a new one (single active OTP per purpose+
 * email). `attempts` is incremented atomically BEFORE the compare; more
 * than otp_max_attempts deletes the row and fails closed. Success deletes
 * the row too (single-use).
 */
class OtpService
{
    public function issue(string $purpose, string $email): string
    {
        $identifier = $this->identifier($purpose, $email);
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        UserVerification::where('identifier', $identifier)->delete();

        UserVerification::create([
            'identifier' => $identifier,
            'value' => Hash::make($otp),
            'expires_at' => now()->addSeconds((int) config('auth_next.otp_expire_sec')),
            'attempts' => 0,
        ]);

        return $otp;
    }

    /**
     * @return array{valid: bool, message: ?string}
     */
    public function verify(string $purpose, string $email, string $otp): array
    {
        $identifier = $this->identifier($purpose, $email);

        return DB::transaction(function () use ($identifier, $otp) {
            $row = UserVerification::where('identifier', $identifier)->lockForUpdate()->first();

            if (! $row) {
                return ['valid' => false, 'message' => 'Invalid or expired OTP'];
            }

            if ($row->expires_at->isPast()) {
                $row->delete();

                return ['valid' => false, 'message' => 'Invalid or expired OTP'];
            }

            // Atomic increment BEFORE compare - a bad guess always costs an attempt.
            $row->increment('attempts');
            $row->refresh();

            if ($row->attempts > (int) config('auth_next.otp_max_attempts')) {
                $row->delete();

                return ['valid' => false, 'message' => 'Too many failed attempts'];
            }

            if (! Hash::check($otp, $row->value)) {
                return ['valid' => false, 'message' => 'Invalid or expired OTP'];
            }

            // Success - single use.
            $row->delete();

            return ['valid' => true, 'message' => null];
        });
    }

    protected function identifier(string $purpose, string $email): string
    {
        return $purpose.':'.strtolower($email);
    }
}

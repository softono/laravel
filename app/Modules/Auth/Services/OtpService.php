<?php

namespace App\Modules\Auth\Services;

use App\Helpers\ApiResult;
use App\Models\Auth\UserVerification;
use App\Repositories\Auth\UserVerificationRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * identifier = "{purpose}:{lowercased email}". The OTP itself is a 6-digit
 * code, argon2id-hashed into `value`. Prior rows for the same identifier
 * are deleted before issuing a new one (single active OTP per purpose+
 * email). `attempts` is incremented atomically BEFORE the compare; more
 * than otp_max_attempts deletes the row and fails closed. Success deletes
 * the row too (single-use).
 */
class OtpService
{
    public function __construct(
        protected UserVerificationRepository $verifications,
    ) {}

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

    public function verify(string $purpose, string $email, string $otp): array
    {
        $identifier = $this->identifier($purpose, $email);

        return DB::transaction(function () use ($identifier, $otp) {
            $row = UserVerification::where('identifier', $identifier)->lockForUpdate()->first();

            if (! $row) {
                return ApiResult::failure('Invalid or expired OTP', [], 422);
            }

            if ($row->expires_at->isPast()) {
                $row->delete();

                return ApiResult::failure('Invalid or expired OTP', [], 422);
            }

            // Atomic increment BEFORE compare - a bad guess always costs an attempt.
            $row->increment('attempts');
            $row->refresh();

            if ($row->attempts > (int) config('auth_next.otp_max_attempts')) {
                $row->delete();

                return ApiResult::failure('Too many failed attempts', [], 422);
            }

            if (! Hash::check($otp, $row->value)) {
                return ApiResult::failure('Invalid or expired OTP', [], 422);
            }

            // Success - single use.
            $row->delete();

            return ApiResult::success();
        });
    }

    protected function identifier(string $purpose, string $email): string
    {
        return $purpose.':'.strtolower($email);
    }
}

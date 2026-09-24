<?php

namespace App\Modules\Auth\Services\Tfa;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;

/**
 * TOTP (authenticator app) 2FA method. Uses config('auth_next.totp_window')
 * (default 1, i.e. ±1 step) to tolerate clock drift.
 */
class TotpMethod
{
    protected Google2FA $engine;

    public function __construct()
    {
        $this->engine = new Google2FA;
        $this->engine->setWindow((int) config('auth_next.totp_window'));
    }

    public function generateSecret(): string
    {
        return $this->engine->generateSecretKey();
    }

    public function encryptSecret(string $secret): string
    {
        return Crypt::encryptString($secret);
    }

    public function decryptSecret(string $encrypted): string
    {
        return Crypt::decryptString($encrypted);
    }

    public function verify(string $encryptedSecret, string $code): bool
    {
        $secret = $this->decryptSecret($encryptedSecret);

        return $this->engine->verifyKey($secret, $code) !== false;
    }

    public function otpAuthUri(string $secret, string $accountName, string $issuer): string
    {
        return $this->engine->getQRCodeUrl($issuer, $accountName, $secret);
    }

    /**
     * Renders the otpauth:// URI as an inline SVG string (no GD/Imagick
     * dependency required - pure PHP).
     */
    public function qrCodeSvg(string $otpAuthUri): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd,
        );

        return (new Writer($renderer))->writeString($otpAuthUri);
    }
}

<?php

namespace App\Support;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Verificación en dos pasos (TOTP, compatible con Google Authenticator, Authy…).
 * Autocontenida sobre pragmarx/google2fa + bacon/bacon-qr-code (SVG, sin GD).
 */
class TwoFactor
{
    protected static function engine(): Google2FA
    {
        return new Google2FA();
    }

    public static function generateSecret(): string
    {
        return static::engine()->generateSecretKey();
    }

    /** URI otpauth:// que codifica el QR. */
    public static function otpauthUri(User $user, string $secret): string
    {
        return static::engine()->getQRCodeUrl(
            config('app.name', 'CMS'),
            $user->email,
            $secret,
        );
    }

    /** QR como SVG embebible (sin dependencias de imagen). */
    public static function qrSvg(User $user, string $secret): string
    {
        $renderer = new ImageRenderer(new RendererStyle(200, 0), new SvgImageBackEnd());

        return (new Writer($renderer))->writeString(static::otpauthUri($user, $secret));
    }

    /** Verifica un código TOTP (ventana ±2 para tolerar desfases de reloj). */
    public static function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);

        if ($code === '' || strlen($code) !== 6) {
            return false;
        }

        return (bool) static::engine()->verifyKey($secret, $code, 2);
    }

    /** @return array<int, string> Códigos de recuperación de un solo uso. */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        return collect(range(1, $count))
            ->map(fn () => Str::upper(Str::random(5) . '-' . Str::random(5)))
            ->all();
    }

    /**
     * Consume un código de recuperación (lo elimina si existe). Devuelve si valía.
     */
    public static function consumeRecoveryCode(User $user, string $code): bool
    {
        $code = trim($code);
        $codes = $user->two_factor_recovery_codes ?? [];

        $index = array_search($code, $codes, true);

        if ($index === false) {
            return false;
        }

        unset($codes[$index]);
        $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

        return true;
    }
}

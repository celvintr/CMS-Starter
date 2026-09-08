<?php

namespace App\Filament\Pages;

use App\Support\TwoFactor;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Seguridad extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Seguridad';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?int $navigationSort = 90;
    protected static string $view = 'filament.pages.seguridad';

    public bool $showingSetup = false;
    public ?string $setupSecret = null;
    public ?string $setupQr = null;
    public string $confirmCode = '';
    public string $disableCode = '';

    /** @var array<int, string>|null Códigos de recuperación recién generados (se muestran una vez). */
    public ?array $recoveryCodes = null;

    public function getTitle(): string
    {
        return 'Seguridad';
    }

    public function twoFactorEnabled(): bool
    {
        return auth()->user()->hasTwoFactorEnabled();
    }

    /** Comienza la activación: genera secreto + QR y muestra el paso de confirmación. */
    public function startSetup(): void
    {
        if ($this->twoFactorEnabled()) {
            return;
        }

        $this->setupSecret = TwoFactor::generateSecret();
        $this->setupQr = TwoFactor::qrSvg(auth()->user(), $this->setupSecret);
        $this->showingSetup = true;
        $this->confirmCode = '';
        $this->recoveryCodes = null;
    }

    public function cancelSetup(): void
    {
        $this->reset(['showingSetup', 'setupSecret', 'setupQr', 'confirmCode']);
    }

    /** Confirma el código del autenticador y activa la 2FA. */
    public function confirmSetup(): void
    {
        if ($this->twoFactorEnabled() || ! $this->setupSecret) {
            return;
        }

        if (! TwoFactor::verify($this->setupSecret, $this->confirmCode)) {
            Notification::make()->title('Código incorrecto. Revisa tu app e intenta de nuevo.')->danger()->send();

            return;
        }

        $codes = TwoFactor::generateRecoveryCodes();

        auth()->user()->forceFill([
            'two_factor_secret' => $this->setupSecret,
            'two_factor_recovery_codes' => $codes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        // El usuario ya demostró posesión: no lo mandamos al desafío en esta sesión.
        session()->put('2fa_passed', true);

        $this->reset(['showingSetup', 'setupSecret', 'setupQr', 'confirmCode']);
        $this->recoveryCodes = $codes;

        Notification::make()->title('Verificación en dos pasos activada.')->success()->send();
    }

    /** Desactiva la 2FA (requiere un código válido o de recuperación). */
    public function disable(): void
    {
        if (! $this->twoFactorEnabled()) {
            return;
        }

        $user = auth()->user();

        if (! TwoFactor::verify($user->two_factor_secret, $this->disableCode) && ! TwoFactor::consumeRecoveryCode($user, $this->disableCode)) {
            Notification::make()->title('Código incorrecto. No se desactivó la 2FA.')->danger()->send();

            return;
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->reset(['disableCode', 'recoveryCodes']);

        Notification::make()->title('Verificación en dos pasos desactivada.')->send();
    }

    /** Regenera los códigos de recuperación (invalida los anteriores). */
    public function regenerateRecoveryCodes(): void
    {
        if (! $this->twoFactorEnabled()) {
            return;
        }

        $codes = TwoFactor::generateRecoveryCodes();
        auth()->user()->forceFill(['two_factor_recovery_codes' => $codes])->save();
        $this->recoveryCodes = $codes;

        Notification::make()->title('Códigos de recuperación regenerados.')->success()->send();
    }
}

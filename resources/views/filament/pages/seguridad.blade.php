<x-filament-panels::page>
    @php $enabled = $this->twoFactorEnabled(); @endphp

    @if (auth()->user()->isAdmin() && ! $enabled)
        <div style="display:flex;gap:12px;align-items:flex-start;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:14px 16px;color:#92400e">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" style="flex-shrink:0;margin-top:1px">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
            </svg>
            <div style="font-size:14px;line-height:1.5">
                <strong>Tu cuenta de administrador requiere verificación en dos pasos.</strong>
                Actívala aquí abajo para poder seguir usando el panel.
            </div>
        </div>
    @endif

    {{-- Códigos de recuperación recién generados (se muestran una sola vez) --}}
    @if ($recoveryCodes)
        <x-filament::section>
            <x-slot name="heading">Guarda tus códigos de recuperación</x-slot>
            <x-slot name="description">Cada código sirve una sola vez para entrar si pierdes tu teléfono. Guárdalos en un lugar seguro; no volverán a mostrarse.</x-slot>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;font-family:ui-monospace,monospace">
                @foreach ($recoveryCodes as $code)
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px;text-align:center;font-size:14px;letter-spacing:1px">{{ $code }}</div>
                @endforeach
            </div>
        </x-filament::section>
    @endif

    @if (! $enabled && ! $showingSetup)
        {{-- Estado: desactivada --}}
        <x-filament::section>
            <x-slot name="heading">Verificación en dos pasos</x-slot>
            <x-slot name="description">Agrega una capa extra de seguridad: además de tu contraseña, pediremos un código temporal de tu teléfono al iniciar sesión.</x-slot>

            <p style="color:#64748b;font-size:14px;margin:0 0 16px">
                Necesitas una app de autenticación como <strong>Google Authenticator</strong>, <strong>Authy</strong> o <strong>Microsoft Authenticator</strong>.
            </p>

            <x-filament::button wire:click="startSetup" icon="heroicon-o-shield-check">
                Activar verificación en dos pasos
            </x-filament::button>
        </x-filament::section>
    @endif

    @if (! $enabled && $showingSetup)
        {{-- Estado: configurando --}}
        <x-filament::section>
            <x-slot name="heading">Escanea el código QR</x-slot>
            <x-slot name="description">Abre tu app de autenticación, escanea este código y luego ingresa los 6 dígitos que te muestre.</x-slot>

            <div style="display:flex;flex-wrap:wrap;gap:28px;align-items:flex-start">
                <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:12px;width:200px;height:200px">
                    {!! $setupQr !!}
                </div>

                <div style="flex:1;min-width:240px">
                    <p style="color:#64748b;font-size:13px;margin:0 0 6px">¿No puedes escanear? Ingresa esta clave manualmente:</p>
                    <code style="display:block;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px;font-size:13px;word-break:break-all;margin-bottom:18px">{{ $setupSecret }}</code>

                    <label style="display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:6px">Código de verificación</label>
                    <input type="text" wire:model="confirmCode" inputmode="numeric" placeholder="000000" maxlength="6"
                           wire:keydown.enter="confirmSetup"
                           style="width:180px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;font-size:18px;letter-spacing:4px;text-align:center;outline:none">

                    <div style="margin-top:18px;display:flex;gap:10px">
                        <x-filament::button wire:click="confirmSetup">Confirmar y activar</x-filament::button>
                        <x-filament::button color="gray" wire:click="cancelSetup">Cancelar</x-filament::button>
                    </div>
                </div>
            </div>
        </x-filament::section>
    @endif

    @if ($enabled)
        {{-- Estado: activada --}}
        <x-filament::section>
            <x-slot name="heading">
                <span style="display:inline-flex;align-items:center;gap:8px">
                    <span style="display:inline-block;width:9px;height:9px;border-radius:999px;background:#16a34a"></span>
                    Verificación en dos pasos activada
                </span>
            </x-slot>
            <x-slot name="description">Tu cuenta está protegida. Al iniciar sesión pediremos un código de tu app de autenticación.</x-slot>

            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:26px">
                <x-filament::button color="gray" wire:click="regenerateRecoveryCodes" icon="heroicon-o-arrow-path">
                    Regenerar códigos de recuperación
                </x-filament::button>
            </div>

            <div style="border-top:1px solid #e2e8f0;padding-top:20px">
                <p style="font-size:14px;font-weight:600;color:#475569;margin:0 0 4px">Desactivar</p>
                <p style="color:#64748b;font-size:13px;margin:0 0 12px">Ingresa un código de tu app (o uno de recuperación) para desactivar la 2FA.</p>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                    <input type="text" wire:model="disableCode" placeholder="Código" maxlength="20"
                           style="width:180px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:10px;font-size:15px;text-align:center;outline:none">
                    <x-filament::button color="danger" wire:click="disable">Desactivar 2FA</x-filament::button>
                </div>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>

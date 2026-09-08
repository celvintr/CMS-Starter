<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verificación en dos pasos en el panel:
 *  - Los ADMINISTRADORES están obligados a tener 2FA: si no la configuraron, se
 *    los manda a la página de Seguridad hasta que la activen.
 *  - Quien tenga 2FA activa (admin o editor) pasa por el desafío una vez por sesión.
 *  - Los editores sin 2FA entran normalmente (opcional para ellos).
 */
class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Lo único que un admin sin 2FA puede abrir mientras la configura.
        $allowedWhileForced = $request->routeIs('filament.admin.pages.seguridad')
            || $request->routeIs('filament.admin.auth.logout');

        if ($user->isAdmin() && ! $user->hasTwoFactorEnabled() && ! $allowedWhileForced) {
            return redirect()->route('filament.admin.pages.seguridad');
        }

        if ($user->hasTwoFactorEnabled() && ! $request->session()->get('2fa_passed')) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}

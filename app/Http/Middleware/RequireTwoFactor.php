<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Si el usuario tiene 2FA activa y todavía no pasó el desafío en esta sesión,
 * lo manda a la pantalla de verificación antes de entrar al panel.
 */
class RequireTwoFactor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->hasTwoFactorEnabled() && ! $request->session()->get('2fa_passed')) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}

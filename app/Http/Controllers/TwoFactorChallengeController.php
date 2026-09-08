<?php

namespace App\Http\Controllers;

use App\Support\TwoFactor;
use Illuminate\Http\Request;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        // Sin 2FA activa no hay nada que verificar.
        if (! $user || ! $user->hasTwoFactorEnabled()) {
            return redirect('/admin');
        }

        // Ya verificado en esta sesión.
        if ($request->session()->get('2fa_passed')) {
            return redirect()->intended('/admin');
        }

        return view('auth.two-factor');
    }

    public function verify(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->hasTwoFactorEnabled()) {
            return redirect('/admin');
        }

        $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ]);

        $code = $request->input('code');

        if (TwoFactor::verify($user->two_factor_secret, $code) || TwoFactor::consumeRecoveryCode($user, $code)) {
            $request->session()->put('2fa_passed', true);

            return redirect()->intended('/admin');
        }

        return back()->withErrors(['code' => 'Código incorrecto. Intenta de nuevo.']);
    }
}

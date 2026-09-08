<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\TwoFactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_accepts_current_otp_and_rejects_wrong(): void
    {
        $secret = TwoFactor::generateSecret();
        $otp = (new Google2FA())->getCurrentOtp($secret);

        $this->assertTrue(TwoFactor::verify($secret, $otp));
        $this->assertFalse(TwoFactor::verify($secret, '000000'));
        $this->assertFalse(TwoFactor::verify($secret, ''));
    }

    public function test_recovery_codes_are_single_use(): void
    {
        $user = User::factory()->create();
        $codes = TwoFactor::generateRecoveryCodes();
        $user->forceFill([
            'two_factor_secret' => TwoFactor::generateSecret(),
            'two_factor_recovery_codes' => $codes,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $this->assertTrue($user->hasTwoFactorEnabled());
        $this->assertTrue(TwoFactor::consumeRecoveryCode($user, $codes[0]));
        $this->assertFalse(TwoFactor::consumeRecoveryCode($user->fresh(), $codes[0]));
        $this->assertCount(7, $user->fresh()->two_factor_recovery_codes);
    }

    public function test_secret_is_stored_encrypted(): void
    {
        $secret = TwoFactor::generateSecret();
        $user = User::factory()->create();
        $user->forceFill(['two_factor_secret' => $secret, 'two_factor_confirmed_at' => now()])->save();

        $raw = DB::table('users')->where('id', $user->id)->value('two_factor_secret');

        $this->assertNotSame($secret, $raw);            // cifrado en la base
        $this->assertSame($secret, $user->fresh()->two_factor_secret); // se descifra al leer
    }
}

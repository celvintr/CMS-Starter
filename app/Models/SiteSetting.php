<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'ai_api_key' => 'encrypted',            // la llave de IA se guarda encriptada
        'stripe_secret_key' => 'encrypted',     // secretos de Stripe encriptados
        'stripe_webhook_secret' => 'encrypted',
        'stripe_enabled' => 'boolean',
        'paypal_secret' => 'encrypted',         // secreto de PayPal encriptado
        'paypal_enabled' => 'boolean',
    ];

    /**
     * ¿Stripe está listo para cobrar? (activado y con secret key)
     */
    public function stripeReady(): bool
    {
        return (bool) $this->stripe_enabled && ! empty($this->stripe_secret_key);
    }

    /**
     * ¿PayPal está listo para cobrar? (activado y con credenciales)
     */
    public function paypalReady(): bool
    {
        return (bool) $this->paypal_enabled && ! empty($this->paypal_client_id) && ! empty($this->paypal_secret);
    }

    /**
     * Devuelve (o crea) la única fila de configuración del sitio.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'site_name' => 'Mi Sitio',
            'primary_color' => '#2563eb',
            'secondary_color' => '#0f172a',
        ]);
    }

    /**
     * Número de WhatsApp en formato apto para wa.me (solo dígitos).
     */
    public function whatsappLink(): ?string
    {
        if (! $this->whatsapp) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $this->whatsapp);

        return $digits ? "https://wa.me/{$digits}" : null;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $guarded = [];

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

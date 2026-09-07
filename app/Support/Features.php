<?php

namespace App\Support;

use App\Models\SiteSetting;

/**
 * Funciones del sitio activables (extensiones). Cada una se prende/apaga
 * desde "Funciones del sitio" y desbloquea su funcionalidad.
 */
class Features
{
    protected static ?SiteSetting $settings = null;

    public static function catalog(): array
    {
        return [
            'tienda' => [
                'name' => 'Tienda',
                'description' => 'Carrito, pagos (Stripe/PayPal/WhatsApp), órdenes, cupones y stock.',
                'icon' => 'heroicon-o-shopping-bag',
                'default' => true,
            ],
            'multilenguaje' => [
                'name' => 'Multilenguaje',
                'description' => 'Traduce páginas, módulos y contenido a varios idiomas, con selector en el sitio.',
                'icon' => 'heroicon-o-language',
                'default' => false,
            ],
            'newsletter' => [
                'name' => 'Newsletter',
                'description' => 'Captura suscriptores y envía campañas por correo.',
                'icon' => 'heroicon-o-envelope',
                'default' => false,
            ],
            'reservas' => [
                'name' => 'Reservas / Citas',
                'description' => 'Agenda de citas con fecha, hora y aviso por correo.',
                'icon' => 'heroicon-o-calendar-days',
                'default' => false,
            ],
            'ia' => [
                'name' => 'Generador con IA',
                'description' => 'Crea módulos y plantillas con IA desde un prompt.',
                'icon' => 'heroicon-o-sparkles',
                'default' => true,
            ],
        ];
    }

    protected static function state(): array
    {
        static::$settings ??= SiteSetting::current();

        return static::$settings->features ?? [];
    }

    public static function enabled(string $key): bool
    {
        $state = static::state();

        if (array_key_exists($key, $state)) {
            return (bool) $state[$key];
        }

        return static::catalog()[$key]['default'] ?? false;
    }

    /** Catálogo con su estado actual, para la UI. */
    public static function all(): array
    {
        return collect(static::catalog())
            ->map(fn ($f, $key) => array_merge($f, ['key' => $key, 'enabled' => static::enabled($key)]))
            ->values()
            ->all();
    }

    /** Refresca la caché de settings (tras guardar). */
    public static function flush(): void
    {
        static::$settings = null;
    }
}

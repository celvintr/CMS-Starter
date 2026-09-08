<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\View;

/**
 * Envuelve el contenido de un correo en una plantilla HTML con la marca del sitio
 * (logo/color/pie). Segura para clientes de correo (tablas + estilos en línea).
 */
class EmailTemplate
{
    public static function render(string $heading, string $bodyHtml, ?string $footerHtml = null): string
    {
        return View::make('emails.layout', [
            'settings' => SiteSetting::current(),
            'heading' => $heading,
            'bodyHtml' => $bodyHtml,
            'footerHtml' => $footerHtml,
        ])->render();
    }
}

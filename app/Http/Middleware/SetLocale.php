<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use App\Support\Features;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enrutado multilenguaje por prefijo de URL (/en, /fr…), con el idioma por
 * defecto servido en la raíz sin prefijo.
 *
 * Se ejecuta como middleware GLOBAL, antes de que el router resuelva la ruta:
 *  - Si la URL trae un prefijo de idioma válido y no-predeterminado, fija el
 *    locale, reescribe la petición para quitar el prefijo (así el router hace
 *    match contra las rutas normales de la raíz) y registra un formateador que
 *    vuelve a anteponer el prefijo en toda URL generada (url(), route()…).
 *  - Si trae el idioma por defecto, uno inválido, o la función está apagada,
 *    redirige (301) a la versión canónica sin prefijo.
 *  - Si no trae prefijo, usa el idioma por defecto.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Estado limpio por si un formateador quedó activo de una petición previa
        // (entornos persistentes como Octane, o la capa de tests en el mismo proceso).
        URL::formatPathUsing(fn (string $p): string => $p);

        $settings = SiteSetting::current();
        $default = $settings->defaultLanguage();

        $path = trim($request->getPathInfo(), '/');
        $segments = $path === '' ? [] : explode('/', $path);
        $first = $segments[0] ?? '';

        // El primer segmento solo se trata como idioma si es un código configurado.
        if ($first !== '' && in_array($first, $settings->languageCodes(), true)) {
            $rest = '/'.implode('/', array_slice($segments, 1));
            $query = $request->getQueryString();

            // Función apagada o idioma por defecto -> URL canónica sin prefijo.
            if (! Features::enabled('multilenguaje') || $first === $default) {
                return redirect($rest.($query ? '?'.$query : ''), 301);
            }

            // Idioma válido y no-predeterminado: activarlo.
            app()->setLocale($first);
            URL::formatPathUsing(fn (string $p): string => $p === '/' ? '/'.$first : '/'.$first.$p);

            // Reescribir la petición sin el prefijo para que el router la resuelva.
            // duplicate() con un nuevo REQUEST_URI reinicia las cachés de path de Symfony
            // (más robusto que initialize(), también bajo la capa de tests).
            $duplicate = $request->duplicate(null, null, null, null, null, array_merge(
                $request->server->all(),
                ['REQUEST_URI' => $rest.($query ? '?'.$query : '')],
            ));
            $duplicate->setUserResolver($request->getUserResolver());
            app()->instance('request', $duplicate);

            $response = $next($duplicate);

            // La respuesta (y sus vistas) ya se generaron con el prefijo; limpiamos el
            // formateador para no filtrarlo a la siguiente petición del mismo proceso.
            URL::formatPathUsing(fn (string $p): string => $p);

            return $response;
        }

        app()->setLocale($default);

        return $next($request);
    }
}

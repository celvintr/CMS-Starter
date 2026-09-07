<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Idioma por prefijo de URL: se ejecuta antes del router (global) para
        // poder reescribir la ruta y quitar el prefijo /en, /fr…
        $middleware->prepend(\App\Http\Middleware\SetLocale::class);

        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);

        // El webhook de Stripe es una petición externa firmada: exenta de CSRF.
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

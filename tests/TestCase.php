<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Las pruebas no dependen de los assets compilados por Vite: sin el
        // manifest (p. ej. en CI, donde no se corre `npm run build`) las vistas
        // que usan @vite lanzarían excepción. Esto lo neutraliza.
        $this->withoutVite();
    }
}

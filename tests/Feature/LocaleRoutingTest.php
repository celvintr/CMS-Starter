<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\SiteSetting;
use App\Support\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::current()->update([
            'features' => ['multilenguaje' => true],
            'default_language' => 'es',
            'languages' => [['code' => 'es', 'name' => 'Español'], ['code' => 'en', 'name' => 'English']],
        ]);
        Features::flush();

        Page::create(['title' => 'Inicio', 'slug' => 'home', 'content' => [], 'is_published' => true]);
        Page::create(['title' => 'Nosotros', 'slug' => 'nosotros', 'content' => [], 'is_published' => true]);
    }

    public function test_default_language_served_at_root(): void
    {
        $this->get('/')->assertOk();
        $this->get('/nosotros')->assertOk();
    }

    public function test_locale_prefix_is_served(): void
    {
        $this->get('/en')->assertOk();
        $this->get('/en/nosotros')->assertOk();
    }

    public function test_default_locale_prefix_redirects_to_canonical_root(): void
    {
        $this->get('/es')->assertRedirect('/');
        $this->get('/es/nosotros')->assertRedirect('/nosotros');
    }

    public function test_unconfigured_locale_is_not_a_locale(): void
    {
        // 'fr' no está configurado: se trata como slug de página y no existe.
        $this->get('/fr/nosotros')->assertNotFound();
    }
}

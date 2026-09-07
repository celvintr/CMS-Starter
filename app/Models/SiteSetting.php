<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use HasTranslations;

    protected $guarded = [];

    protected $casts = [
        'languages' => 'array',
        'translations' => 'array',
        'ai_api_key' => 'encrypted',            // la llave de IA se guarda encriptada
        'stripe_secret_key' => 'encrypted',     // secretos de Stripe encriptados
        'stripe_webhook_secret' => 'encrypted',
        'stripe_enabled' => 'boolean',
        'paypal_secret' => 'encrypted',         // secreto de PayPal encriptado
        'paypal_enabled' => 'boolean',
        'menu' => 'array',
        'features' => 'array',
    ];

    /**
     * Enlaces del menú de navegación. Usa el menú personalizado si existe;
     * si no, arma uno automático con páginas + módulos públicos + blog.
     *
     * @return array<int, array{label:string,url:string,new_tab:bool}>
     */
    public function menuLinks(): array
    {
        $items = $this->menu ?? [];

        if (! empty($items)) {
            return collect($items)
                ->filter(fn ($it) => ! empty($it['label']))
                ->map(function ($it) {
                    $url = match ($it['type'] ?? 'custom') {
                        'home' => url('/'),
                        'blog' => route('blog.index'),
                        'page' => url('/' . ($it['page'] ?? '')),
                        'module' => ! empty($it['module']) ? url('/m/' . $it['module']) : '#',
                        default => $it['url'] ?? '#',
                    };

                    return ['label' => $it['label'], 'url' => $url, 'new_tab' => ! empty($it['new_tab'])];
                })
                ->values()
                ->all();
        }

        // Fallback automático
        $links = [];
        foreach (Page::published()->where('show_in_menu', true)->orderBy('sort_order')->orderBy('title')->get() as $p) {
            $links[] = ['label' => $p->title, 'url' => url('/' . $p->slug), 'new_tab' => false];
        }
        foreach (Module::where('is_public', true)->orderBy('sort_order')->orderBy('name')->get() as $m) {
            $links[] = ['label' => $m->pluralLabel(), 'url' => url('/m/' . $m->slug), 'new_tab' => false];
        }
        $links[] = ['label' => 'Blog', 'url' => route('blog.index'), 'new_tab' => false];

        return $links;
    }

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
    public function defaultLanguage(): string
    {
        return $this->default_language ?: 'es';
    }

    /** @return array<int, array{code:string,name:string}> */
    public function activeLanguages(): array
    {
        $langs = $this->languages ?? [];
        if (empty($langs)) {
            return [['code' => $this->defaultLanguage(), 'name' => strtoupper($this->defaultLanguage())]];
        }

        return $langs;
    }

    public function languageCodes(): array
    {
        return array_values(array_filter(array_map(fn ($l) => $l['code'] ?? null, $this->activeLanguages())));
    }

    public function whatsappLink(): ?string
    {
        if (! $this->whatsapp) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $this->whatsapp);

        return $digits ? "https://wa.me/{$digits}" : null;
    }
}

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
        'cookie_banner' => 'boolean',
        'shipping_enabled' => 'boolean',
        'shipping_cost' => 'decimal:2',
        'shipping_free_from' => 'decimal:2',
        'reservation_days' => 'array',
    ];

    // --- Reservas: horarios y disponibilidad -------------------------------

    /** Días abiertos en formato ISO (1=lunes … 7=domingo). Por defecto L-V. */
    public function reservationDays(): array
    {
        $days = $this->reservation_days ?: [];

        return ! empty($days) ? array_map('intval', $days) : [1, 2, 3, 4, 5];
    }

    /**
     * Horarios posibles ("HH:MM") de una fecha según día abierto, apertura,
     * cierre y duración del turno. No consulta la base (lógica pura).
     *
     * @return array<int, string>
     */
    public function reservationSlots(\Illuminate\Support\Carbon $date): array
    {
        if (! in_array($date->dayOfWeekIso, $this->reservationDays(), true)) {
            return [];
        }

        $step = max(5, (int) ($this->reservation_slot_minutes ?: 30));
        $open = \Illuminate\Support\Carbon::parse($date->toDateString() . ' ' . ($this->reservation_open ?: '09:00'));
        $close = \Illuminate\Support\Carbon::parse($date->toDateString() . ' ' . ($this->reservation_close ?: '17:00'));

        $slots = [];
        for ($t = $open->copy(); $t->lt($close); $t->addMinutes($step)) {
            $slots[] = $t->format('H:i');
        }

        return $slots;
    }

    /**
     * Horarios realmente disponibles de una fecha: quita los que ya alcanzaron
     * la capacidad y, si la fecha es hoy, los que ya pasaron.
     *
     * @return array<int, string>
     */
    public function availableReservationSlots(\Illuminate\Support\Carbon $date): array
    {
        $slots = $this->reservationSlots($date);

        if (empty($slots)) {
            return [];
        }

        $capacity = max(1, (int) ($this->reservation_capacity ?: 1));
        $taken = [];

        if (\Illuminate\Support\Facades\Schema::hasTable('reservations')) {
            $taken = Reservation::query()
                ->whereDate('starts_at', $date->toDateString())
                ->where('status', '!=', 'canceled')
                ->get()
                ->groupBy(fn ($r) => $r->starts_at->format('H:i'))
                ->map->count()
                ->all();
        }

        $isToday = $date->isToday();
        $now = now();

        return array_values(array_filter($slots, function ($slot) use ($taken, $capacity, $isToday, $date, $now) {
            if (($taken[$slot] ?? 0) >= $capacity) {
                return false;
            }

            if ($isToday && \Illuminate\Support\Carbon::parse($date->toDateString() . ' ' . $slot)->lte($now)) {
                return false;
            }

            return true;
        }));
    }

    /**
     * Costo de envío para un subtotal dado. 0 si el envío está apagado o si el
     * subtotal alcanza el umbral de envío gratis.
     */
    public function shippingFor(float $subtotal): float
    {
        if (! $this->shipping_enabled) {
            return 0.0;
        }

        if ($this->shipping_free_from !== null && $subtotal >= (float) $this->shipping_free_from) {
            return 0.0;
        }

        return round((float) $this->shipping_cost, 2);
    }

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

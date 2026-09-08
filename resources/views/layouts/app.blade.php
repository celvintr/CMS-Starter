<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if ($settings->favicon_path)
        <link rel="icon" href="{{ asset('storage/' . $settings->favicon_path) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/' . $settings->favicon_path) }}">
    @endif
    <title>@yield('title', $settings->t('meta_title') ?: $settings->t('site_name'))</title>
    <meta name="description" content="@yield('meta_description', $settings->t('meta_description'))">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- Alternates de idioma (hreflang) para SEO multilenguaje --}}
    @php
        $mlEnabled = \App\Support\Features::enabled('multilenguaje');
        $siteLangs = $settings->activeLanguages();

        // El middleware SetLocale ya quitó el prefijo de idioma de la petición,
        // así que la ruta actual viene limpia.
        $basePath = request()->path();          // "blog", "nosotros" o "/" en el inicio
        $basePath = $basePath === '/' ? '' : trim($basePath, '/');
        $queryString = request()->getQueryString();
        $origin = request()->getSchemeAndHttpHost();

        // Construye la misma página en otro idioma. Se arma a mano (sin url()/route())
        // para no heredar el prefijo del idioma activo.
        $localeUrl = function (array $lang) use ($settings, $basePath, $queryString, $origin) {
            $isDefault = $lang['code'] === $settings->defaultLanguage();
            $path = $isDefault ? $basePath : trim($lang['code'] . '/' . $basePath, '/');
            $url = $origin . '/' . $path;

            return $queryString ? $url . '?' . $queryString : $url;
        };
    @endphp
    @if ($mlEnabled && count($siteLangs) > 1)
        @foreach ($siteLangs as $lang)
            <link rel="alternate" hreflang="{{ $lang['code'] }}" href="{{ $localeUrl($lang) }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ $origin . '/' . $basePath }}">
    @endif

    {{-- Open Graph / Twitter (para compartir en redes y WhatsApp) --}}
    <meta property="og:site_name" content="{{ $settings->t('site_name') }}">
    <meta property="og:title" content="@yield('og_title', $settings->t('meta_title') ?: $settings->t('site_name'))">
    <meta property="og:description" content="@yield('meta_description', $settings->t('meta_description'))">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @elseif ($settings->logo_path)
        <meta property="og:image" content="{{ asset('storage/' . $settings->logo_path) }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">

    {{-- Datos estructurados: Organización --}}
    @php
        $org = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $settings->t('site_name'),
            'url' => url('/'),
            'logo' => $settings->logo_path ? asset('storage/' . $settings->logo_path) : null,
            'telephone' => $settings->phone,
            'email' => $settings->email,
            'sameAs' => array_values(array_filter([$settings->facebook, $settings->instagram, $settings->tiktok])),
        ]);
    @endphp
    <script type="application/ld+json">{!! json_encode($org, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    @yield('head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite('resources/css/app.css')

    <style>
        :root {
            --brand: {{ $settings->primary_color ?: '#2563eb' }};
            --brand-ink: {{ $settings->secondary_color ?: '#0f172a' }};
        }
        html { scroll-behavior: smooth; }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } *, *::before, *::after { animation-duration: .001ms !important; transition-duration: .001ms !important; } }
        @keyframes riseIn { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        .rise { animation: riseIn .7s cubic-bezier(.2,.7,.2,1) both; }
    </style>

    {{-- Analítica / seguimiento. Si el banner de cookies está activo, se difiere
         hasta que el visitante acepte (se guarda en un <template>). --}}
    @php
        $trackHead = trim((string) $settings->analytics_head);
        $trackBody = trim((string) $settings->analytics_body);
        $cookieBanner = (bool) $settings->cookie_banner;
        $consentGate = $cookieBanner && ($trackHead !== '' || $trackBody !== '');
    @endphp
    @if ($trackHead !== '')
        @if ($consentGate)
            <template id="__track_head">{!! $trackHead !!}</template>
        @else
            {!! $trackHead !!}
        @endif
    @endif
</head>
<body class="font-sans bg-[#fafaf9] text-slate-700 antialiased">

    {{-- HEADER --}}
    <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-[#fafaf9]/80 backdrop-blur-md">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                @if ($settings->logo_path)
                    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="{{ $settings->t('site_name') }}" class="h-9 w-auto">
                @else
                    <span class="font-display text-xl font-extrabold tracking-tight text-brandink">{{ $settings->t('site_name') }}</span>
                @endif
            </a>

            <nav class="hidden md:flex items-center gap-7">
                @foreach ($settings->menuLinks() as $link)
                    <a href="{{ $link['url'] }}" @if($link['new_tab']) target="_blank" rel="noopener" @endif class="nav-link">{{ $link['label'] }}</a>
                @endforeach

                <a href="{{ route('search') }}" class="text-slate-600 hover:text-brand transition-colors" aria-label="Buscar">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>
                </a>

                @if ($mlEnabled && count($siteLangs) > 1)
                    <div class="flex items-center gap-1 text-xs font-semibold" aria-label="Idioma">
                        @foreach ($siteLangs as $lang)
                            <a href="{{ $localeUrl($lang) }}" hreflang="{{ $lang['code'] }}"
                               @class(['px-1.5 py-1 rounded uppercase tracking-wide transition-colors', 'text-brand' => app()->getLocale() === $lang['code'], 'text-slate-400 hover:text-slate-700' => app()->getLocale() !== $lang['code']])
                               title="{{ $lang['name'] }}">{{ $lang['code'] }}</a>
                        @endforeach
                    </div>
                @endif

                @php $hasShop = ($menuModules ?? collect())->contains(fn ($m) => $m->type === 'tienda'); $cartCount = collect(session('cart', []))->sum(); @endphp
                @if ($hasShop)
                    <a href="{{ route('cart.index') }}" class="relative inline-flex items-center text-slate-600 hover:text-brand transition-colors">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                        @if ($cartCount > 0)
                            <span class="absolute -top-2 -right-2 inline-flex items-center justify-center text-[10px] font-bold text-white rounded-full h-[18px] min-w-[18px] px-1 bg-brand">{{ $cartCount }}</span>
                        @endif
                    </a>
                @endif

                @if ($settings->whatsappLink())
                    <a href="{{ $settings->whatsappLink() }}" target="_blank" rel="noopener" class="btn-primary !px-5 !py-2.5 text-[15px]">Contáctanos</a>
                @endif
            </nav>

            {{-- Botón móvil --}}
            <button type="button" onclick="document.getElementById('m-nav').classList.toggle('hidden')"
                    class="md:hidden inline-flex items-center justify-center h-10 w-10 rounded-lg border border-slate-200 text-slate-700" aria-label="Menú">
                <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
        </div>

        {{-- Panel móvil --}}
        <div id="m-nav" class="hidden md:hidden border-t border-slate-200/70 bg-[#fafaf9]">
            <nav class="max-w-6xl mx-auto px-4 py-3 flex flex-col gap-1">
                @foreach ($settings->menuLinks() as $link)
                    <a href="{{ $link['url'] }}" @if($link['new_tab']) target="_blank" rel="noopener" @endif class="py-2 nav-link">{{ $link['label'] }}</a>
                @endforeach
                @if (($menuModules ?? collect())->contains(fn ($m) => $m->type === 'tienda'))
                    <a href="{{ route('cart.index') }}" class="py-2 nav-link">Carrito</a>
                @endif
                @if ($mlEnabled && count($siteLangs) > 1)
                    <div class="flex items-center gap-1 pt-2 mt-1 border-t border-slate-200/70 text-xs font-semibold" aria-label="Idioma">
                        @foreach ($siteLangs as $lang)
                            <a href="{{ $localeUrl($lang) }}" hreflang="{{ $lang['code'] }}"
                               @class(['px-2 py-1 rounded uppercase tracking-wide', 'text-brand' => app()->getLocale() === $lang['code'], 'text-slate-500' => app()->getLocale() !== $lang['code']])
                               title="{{ $lang['name'] }}">{{ $lang['code'] }}</a>
                        @endforeach
                    </div>
                @endif
            </nav>
        </div>
    </header>

    {{-- FLASH --}}
    @if (session('sent'))
        <div class="max-w-6xl mx-auto px-4 pt-4">
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
                {{ session('sent') }}
            </div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="mt-24 text-slate-300" style="background: var(--brand-ink)">
        <div class="max-w-6xl mx-auto px-4 py-14 grid gap-10 md:grid-cols-3">
            <div>
                <div class="font-display text-white text-xl font-extrabold tracking-tight">{{ $settings->t('site_name') }}</div>
                @if ($settings->t('footer_text'))
                    <p class="mt-3 text-sm leading-relaxed text-slate-400 max-w-xs">{{ $settings->t('footer_text') }}</p>
                @endif
            </div>
            <div class="text-sm space-y-2.5">
                <div class="text-white/90 font-semibold mb-3 uppercase tracking-wider text-xs">Contacto</div>
                @if ($settings->phone)<div class="text-slate-400">{{ $settings->phone }}</div>@endif
                @if ($settings->whatsapp)<div class="text-slate-400">WhatsApp {{ $settings->whatsapp }}</div>@endif
                @if ($settings->email)<div class="text-slate-400">{{ $settings->email }}</div>@endif
                @if ($settings->address)<div class="text-slate-400">{{ $settings->address }}</div>@endif
            </div>
            <div class="text-sm">
                <div class="text-white/90 font-semibold mb-3 uppercase tracking-wider text-xs">Síguenos</div>
                <div class="flex flex-col gap-2">
                    @if ($settings->facebook)<a href="{{ $settings->facebook }}" target="_blank" rel="noopener" class="text-slate-400 hover:text-white transition-colors">Facebook</a>@endif
                    @if ($settings->instagram)<a href="{{ $settings->instagram }}" target="_blank" rel="noopener" class="text-slate-400 hover:text-white transition-colors">Instagram</a>@endif
                    @if ($settings->tiktok)<a href="{{ $settings->tiktok }}" target="_blank" rel="noopener" class="text-slate-400 hover:text-white transition-colors">TikTok</a>@endif
                </div>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="max-w-6xl mx-auto px-4 py-5 text-xs text-slate-500">
                © {{ date('Y') }} {{ $settings->t('site_name') }}. Todos los derechos reservados.
            </div>
        </div>
    </footer>

    {{-- WHATSAPP FLOTANTE --}}
    @if ($settings->whatsappLink())
        <a href="{{ $settings->whatsappLink() }}" target="_blank" rel="noopener"
           class="fixed bottom-5 right-5 z-50 h-14 w-14 rounded-full bg-[#25D366] text-white flex items-center justify-center shadow-lg shadow-emerald-500/30 hover:scale-105 transition-transform"
           aria-label="WhatsApp">
            <svg viewBox="0 0 24 24" class="h-7 w-7" fill="currentColor"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.599 5.336l-.999 3.648 3.9-1.283zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.767.967-.94 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
        </a>
    @endif

    {{-- Analítica (final del body) --}}
    @if ($trackBody !== '')
        @if ($consentGate)
            <template id="__track_body">{!! $trackBody !!}</template>
        @else
            {!! $trackBody !!}
        @endif
    @endif

    {{-- Banner de cookies --}}
    @if ($cookieBanner)
        <div id="__cookie" hidden
             style="position:fixed;bottom:16px;left:16px;right:16px;z-index:60;max-width:640px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 10px 40px -12px rgba(15,23,42,.3);padding:16px 18px;display:flex;gap:14px;align-items:center;flex-wrap:wrap">
            <p style="margin:0;flex:1;min-width:220px;font-size:13px;color:#475569;line-height:1.5">
                {{ $settings->cookie_text ?: 'Usamos cookies para mejorar tu experiencia y analizar el tráfico.' }}
                @if ($settings->cookie_policy_url)
                    <a href="{{ $settings->cookie_policy_url }}" style="color:var(--brand);text-decoration:underline">Más información</a>
                @endif
            </p>
            <div style="display:flex;gap:8px">
                <button type="button" data-cookie="rejected" style="padding:8px 16px;border:1px solid #cbd5e1;background:#fff;color:#475569;border-radius:999px;font-size:13px;font-weight:600;cursor:pointer">Rechazar</button>
                <button type="button" data-cookie="accepted" class="bg-brand" style="padding:8px 16px;border:0;color:#fff;border-radius:999px;font-size:13px;font-weight:700;cursor:pointer">Aceptar</button>
            </div>
        </div>
    @endif

    @if ($cookieBanner || $consentGate)
        <script>
            (function () {
                function activate() {
                    ['__track_head', '__track_body'].forEach(function (id) {
                        var t = document.getElementById(id);
                        if (!t || !t.content) return;
                        var frag = t.content.cloneNode(true);
                        frag.querySelectorAll('script').forEach(function (old) {
                            var s = document.createElement('script');
                            for (var i = 0; i < old.attributes.length; i++) {
                                s.setAttribute(old.attributes[i].name, old.attributes[i].value);
                            }
                            s.text = old.textContent;
                            old.parentNode.replaceChild(s, old);
                        });
                        (id === '__track_head' ? document.head : document.body).appendChild(frag);
                        t.remove();
                    });
                }

                var gated = {{ $consentGate ? 'true' : 'false' }};
                var consent = null;
                try { consent = localStorage.getItem('cookie_consent'); } catch (e) {}

                if (gated && consent === 'accepted') activate();

                var banner = document.getElementById('__cookie');
                if (banner) {
                    if (!consent) banner.hidden = false;
                    banner.querySelectorAll('[data-cookie]').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            var v = btn.getAttribute('data-cookie');
                            try { localStorage.setItem('cookie_consent', v); } catch (e) {}
                            banner.hidden = true;
                            if (v === 'accepted' && gated) activate();
                        });
                    });
                }
            })();
        </script>
    @endif

</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $settings->meta_title ?: $settings->site_name)</title>
    <meta name="description" content="@yield('meta_description', $settings->meta_description)">

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
</head>
<body class="font-sans bg-[#fafaf9] text-slate-700 antialiased">

    {{-- HEADER --}}
    <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-[#fafaf9]/80 backdrop-blur-md">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                @if ($settings->logo_path)
                    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="{{ $settings->site_name }}" class="h-9 w-auto">
                @else
                    <span class="font-display text-xl font-extrabold tracking-tight text-brandink">{{ $settings->site_name }}</span>
                @endif
            </a>

            <nav class="hidden md:flex items-center gap-7">
                @foreach ($menuPages as $mp)
                    <a href="{{ url('/' . $mp->slug) }}" class="nav-link">{{ $mp->title }}</a>
                @endforeach
                @foreach (($menuModules ?? []) as $mod)
                    <a href="{{ route('module.index', $mod->slug) }}" class="nav-link">{{ $mod->pluralLabel() }}</a>
                @endforeach
                <a href="{{ route('blog.index') }}" class="nav-link">Blog</a>

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
                @foreach ($menuPages as $mp)
                    <a href="{{ url('/' . $mp->slug) }}" class="py-2 nav-link">{{ $mp->title }}</a>
                @endforeach
                @foreach (($menuModules ?? []) as $mod)
                    <a href="{{ route('module.index', $mod->slug) }}" class="py-2 nav-link">{{ $mod->pluralLabel() }}</a>
                @endforeach
                <a href="{{ route('blog.index') }}" class="py-2 nav-link">Blog</a>
                @if (($menuModules ?? collect())->contains(fn ($m) => $m->type === 'tienda'))
                    <a href="{{ route('cart.index') }}" class="py-2 nav-link">Carrito</a>
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
                <div class="font-display text-white text-xl font-extrabold tracking-tight">{{ $settings->site_name }}</div>
                @if ($settings->footer_text)
                    <p class="mt-3 text-sm leading-relaxed text-slate-400 max-w-xs">{{ $settings->footer_text }}</p>
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
                © {{ date('Y') }} {{ $settings->site_name }}. Todos los derechos reservados.
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

</body>
</html>

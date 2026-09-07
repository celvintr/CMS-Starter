@php
    $img = ($data['image'] ?? null) ? asset('storage/' . $data['image']) : null;
@endphp

@if ($img)
    {{-- Hero con imagen: overlay oscuro y texto blanco --}}
    <section class="relative overflow-hidden">
        <img src="{{ $img }}" alt="" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0" style="background: linear-gradient(180deg, color-mix(in srgb, var(--brand-ink) 55%, transparent), color-mix(in srgb, var(--brand-ink) 85%, transparent));"></div>
        <div class="relative max-w-5xl mx-auto px-4 py-32 md:py-44 text-center text-white">
            @if (!empty($settings->tagline))
                <span class="eyebrow !text-white/80 before:!bg-white/50">{{ $settings->tagline }}</span>
            @endif
            <h1 class="mt-5 font-display text-5xl md:text-7xl font-extrabold tracking-tight leading-[1.02] rise">{{ $data['heading'] ?? '' }}</h1>
            @if (!empty($data['subheading']))
                <p class="mt-6 text-lg md:text-xl text-white/85 max-w-2xl mx-auto leading-relaxed">{{ $data['subheading'] }}</p>
            @endif
            @if (!empty($data['button_text']))
                <div class="mt-9"><a href="{{ $data['button_url'] ?? '#contacto' }}" class="btn-primary text-lg">{{ $data['button_text'] }}</a></div>
            @endif
        </div>
    </section>
@else
    {{-- Hero claro con resplandor de marca --}}
    <section class="relative overflow-hidden bg-white">
        <div class="pointer-events-none absolute inset-x-0 -top-40 h-[520px]"
             style="background: radial-gradient(60% 60% at 50% 0%, color-mix(in srgb, var(--brand) 18%, transparent), transparent 70%);"></div>
        <div class="pointer-events-none absolute inset-0 opacity-[0.4]"
             style="background-image: radial-gradient(color-mix(in srgb, var(--brand-ink) 8%, transparent) 1px, transparent 1px); background-size: 22px 22px; mask-image: linear-gradient(180deg, black, transparent 55%);"></div>

        <div class="relative max-w-4xl mx-auto px-4 pt-28 pb-24 md:pt-36 md:pb-32 text-center">
            @if (!empty($settings->tagline))
                <span class="eyebrow rise">{{ $settings->tagline }}</span>
            @endif
            <h1 class="mt-5 font-display text-5xl md:text-7xl font-extrabold tracking-tight leading-[1.02] text-brandink rise">{{ $data['heading'] ?? '' }}</h1>
            @if (!empty($data['subheading']))
                <p class="mt-6 text-lg md:text-xl text-slate-500 max-w-2xl mx-auto leading-relaxed">{{ $data['subheading'] }}</p>
            @endif
            @if (!empty($data['button_text']))
                <div class="mt-9 flex items-center justify-center gap-3">
                    <a href="{{ $data['button_url'] ?? '#contacto' }}" class="btn-primary text-lg">{{ $data['button_text'] }}</a>
                </div>
            @endif
        </div>
    </section>
@endif

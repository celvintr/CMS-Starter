<section class="py-16 md:py-20">
    <div class="max-w-5xl mx-auto px-4">
        <div class="relative overflow-hidden rounded-[28px] px-8 py-16 md:px-16 md:py-20 text-center text-white"
             style="background: var(--brand-ink);">
            <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full"
                 style="background: radial-gradient(circle, color-mix(in srgb, var(--brand) 55%, transparent), transparent 70%);"></div>
            <div class="relative">
                <h2 class="font-display text-3xl md:text-5xl font-extrabold tracking-tight leading-tight">{{ $data['heading'] ?? '' }}</h2>
                @if (!empty($data['text']))
                    <p class="mt-4 text-white/75 max-w-2xl mx-auto text-lg">{{ $data['text'] }}</p>
                @endif
                @if (!empty($data['button_text']))
                    <div class="mt-9">
                        <a href="{{ $data['button_url'] ?? '#contacto' }}"
                           class="inline-flex items-center justify-center rounded-full bg-white px-8 py-3.5 font-semibold hover:opacity-90 transition" style="color: var(--brand-ink)">
                            {{ $data['button_text'] }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

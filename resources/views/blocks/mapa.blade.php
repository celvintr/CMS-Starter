<section class="max-w-6xl mx-auto px-4 py-20 md:py-24">
    @if (!empty($data['heading']))
        <div class="max-w-2xl mb-8">
            <span class="eyebrow">Ubicación</span>
            <h2 class="section-title mt-4">{{ $data['heading'] }}</h2>
        </div>
    @endif

    @if (!empty($data['address']))
        <div class="rounded-2xl overflow-hidden border border-slate-200/80 shadow-sm">
            <iframe
                src="https://www.google.com/maps?q={{ urlencode($data['address']) }}&output=embed"
                width="100%" height="420" style="border:0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen title="Mapa"></iframe>
        </div>
    @endif
</section>

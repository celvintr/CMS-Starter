@php $data = $data ?? []; @endphp
@if (\App\Support\Features::enabled('newsletter'))
    <section class="max-w-4xl mx-auto px-4 py-16">
        <div class="rounded-3xl p-8 md:p-14 text-center" style="background: var(--brand-ink)">
            <h2 class="font-display text-3xl md:text-4xl font-extrabold text-white">
                {{ $data['heading'] ?? 'Suscríbete a nuestro boletín' }}
            </h2>
            @if (! empty($data['subheading']))
                <p class="mt-3 text-slate-300 max-w-xl mx-auto leading-relaxed">{{ $data['subheading'] }}</p>
            @endif

            <form method="POST" action="{{ route('newsletter.subscribe') }}" class="mt-7 flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                @csrf
                <input type="hidden" name="source" value="bloque">
                {{-- Trampa anti-spam --}}
                <input type="text" name="_gotcha" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
                <input type="email" name="email" required placeholder="Tu correo electrónico"
                       class="flex-1 rounded-full px-5 py-3 outline-none focus:ring-2 focus:ring-brand/50">
                <button type="submit" class="rounded-full px-7 py-3 font-bold bg-brand text-white hover:opacity-90 transition">
                    {{ $data['button_text'] ?? 'Suscribirme' }}
                </button>
            </form>
        </div>
    </section>
@endif

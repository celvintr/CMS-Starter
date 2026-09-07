<section class="max-w-6xl mx-auto px-4 py-20 md:py-24">
    @if (!empty($data['heading']))
        <div class="text-center max-w-2xl mx-auto mb-14">
            <span class="eyebrow justify-center">Planes</span>
            <h2 class="section-title mt-4">{{ $data['heading'] }}</h2>
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-3 items-stretch">
        @foreach (($data['plans'] ?? []) as $plan)
            @php $featured = !empty($plan['featured']); @endphp
            <div class="rounded-2xl border p-8 flex flex-col {{ $featured ? 'text-white shadow-xl md:-mt-4 md:mb-4' : 'bg-white border-slate-200/80' }}"
                 @if ($featured) style="background: var(--brand-ink); border-color: var(--brand-ink)" @endif>
                @if ($featured)<span class="self-start text-xs font-semibold uppercase tracking-wider px-3 py-1 rounded-full mb-4" style="background: var(--brand)">Recomendado</span>@endif
                <h3 class="font-display text-xl font-bold {{ $featured ? 'text-white' : '' }}" @if(!$featured) style="color: var(--brand-ink)" @endif>{{ $plan['name'] ?? '' }}</h3>
                <div class="mt-3 flex items-end gap-1">
                    <span class="font-display text-4xl font-extrabold {{ $featured ? 'text-white' : 'text-brand' }}">{{ $plan['price'] ?? '' }}</span>
                    @if (!empty($plan['period']))<span class="mb-1 text-sm {{ $featured ? 'text-white/60' : 'text-slate-400' }}">{{ $plan['period'] }}</span>@endif
                </div>
                <ul class="mt-6 space-y-2.5 flex-1">
                    @foreach (preg_split('/\r\n|\r|\n/', (string)($plan['features'] ?? '')) as $feat)
                        @if (trim($feat) !== '')
                            <li class="flex items-start gap-2 text-sm {{ $featured ? 'text-white/85' : 'text-slate-600' }}">
                                <svg viewBox="0 0 24 24" class="h-5 w-5 flex-none {{ $featured ? 'text-white' : 'text-brand' }}" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                {{ trim($feat) }}
                            </li>
                        @endif
                    @endforeach
                </ul>
                <a href="{{ $plan['button_url'] ?? '#contacto' }}"
                   class="mt-8 w-full inline-flex justify-center rounded-full px-6 py-3 font-semibold transition hover:opacity-90 {{ $featured ? 'bg-white' : 'text-white' }}"
                   @if ($featured) style="color: var(--brand-ink)" @else style="background: var(--brand)" @endif>
                    {{ $plan['button_text'] ?? 'Elegir' }}
                </a>
            </div>
        @endforeach
    </div>
</section>

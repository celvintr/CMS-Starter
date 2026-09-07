<section class="max-w-6xl mx-auto px-4 py-20 md:py-24">
    @if (!empty($data['heading']))
        <div class="max-w-2xl mb-14">
            <span class="eyebrow">Testimonios</span>
            <h2 class="section-title mt-4">{{ $data['heading'] }}</h2>
        </div>
    @endif

    <div class="grid gap-5 md:grid-cols-3">
        @foreach (($data['items'] ?? []) as $item)
            <figure class="card p-7 flex flex-col">
                <svg viewBox="0 0 24 24" class="h-8 w-8 text-brand/30" fill="currentColor"><path d="M7.17 6A5.17 5.17 0 002 11.17V18h6.83v-6.83H5.5A1.67 1.67 0 017.17 9.5zM17.5 6a5.17 5.17 0 00-5.17 5.17V18h6.84v-6.83H15.83A1.67 1.67 0 0117.5 9.5z"/></svg>
                <blockquote class="mt-4 flex-1 text-slate-600 leading-relaxed">{{ $item['quote'] ?? '' }}</blockquote>
                <figcaption class="mt-5 flex items-center gap-3">
                    @if (!empty($item['photo']))
                        <img src="{{ asset('storage/' . $item['photo']) }}" alt="{{ $item['author'] ?? '' }}" class="h-11 w-11 rounded-full object-cover">
                    @else
                        <span class="h-11 w-11 rounded-full flex items-center justify-center text-white font-bold" style="background: var(--brand)">{{ mb_substr($item['author'] ?? '?', 0, 1) }}</span>
                    @endif
                    <span>
                        <span class="block font-bold" style="color: var(--brand-ink)">{{ $item['author'] ?? '' }}</span>
                        @if (!empty($item['role']))<span class="block text-sm text-slate-400">{{ $item['role'] }}</span>@endif
                    </span>
                </figcaption>
            </figure>
        @endforeach
    </div>
</section>

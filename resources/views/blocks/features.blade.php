<section class="max-w-6xl mx-auto px-4 py-20 md:py-24">
    @if (!empty($data['heading']))
        <div class="max-w-2xl mb-14">
            <span class="eyebrow">Lo que ofrecemos</span>
            <h2 class="section-title mt-4">{{ $data['heading'] }}</h2>
        </div>
    @endif

    <div class="grid gap-5 md:grid-cols-3">
        @foreach (($data['items'] ?? []) as $item)
            <div class="card p-7">
                @if (!empty($item['icon']))
                    <div class="icon-tile">
                        @include('partials.icon', ['name' => $item['icon'], 'class' => 'h-6 w-6'])
                    </div>
                @endif
                <h3 class="mt-5 font-display text-xl font-bold tracking-tight text-brandink">{{ $item['title'] ?? '' }}</h3>
                @if (!empty($item['text']))
                    <p class="mt-2.5 text-slate-500 leading-relaxed">{{ $item['text'] }}</p>
                @endif
            </div>
        @endforeach
    </div>
</section>

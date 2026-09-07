<section class="py-16 md:py-20">
    <div class="max-w-5xl mx-auto px-4">
        @if (!empty($data['heading']))
            <h2 class="section-title text-center mb-12">{{ $data['heading'] }}</h2>
        @endif
        <div class="grid gap-6 grid-cols-2 md:grid-cols-4 text-center">
            @foreach (($data['items'] ?? []) as $item)
                <div>
                    <div class="font-display text-4xl md:text-5xl font-extrabold text-brand">{{ $item['number'] ?? '' }}</div>
                    <div class="mt-2 text-sm text-slate-500">{{ $item['label'] ?? '' }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

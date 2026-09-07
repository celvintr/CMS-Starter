<section class="max-w-6xl mx-auto px-4 py-16">
    @if (!empty($data['heading']))
        <h2 class="text-3xl font-extrabold text-center mb-10" style="color: var(--brand-ink)">{{ $data['heading'] }}</h2>
    @endif
    <div class="grid gap-4 grid-cols-2 md:grid-cols-3">
        @foreach (($data['images'] ?? []) as $img)
            <img src="{{ asset('storage/' . $img) }}" alt=""
                 class="w-full h-56 object-cover rounded-xl shadow-sm hover:opacity-90 transition">
        @endforeach
    </div>
</section>

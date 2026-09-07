@php
    $img = ($data['image'] ?? null) ? asset('storage/' . $data['image']) : 'https://placehold.co/600x450?text=Imagen';
    $right = ($data['image_side'] ?? 'left') === 'right';
@endphp
<section class="max-w-6xl mx-auto px-4 py-16">
    <div class="grid gap-10 md:grid-cols-2 items-center">
        <div class="{{ $right ? 'md:order-2' : '' }}">
            <img src="{{ $img }}" alt="" class="w-full rounded-2xl shadow-md object-cover">
        </div>
        <div class="{{ $right ? 'md:order-1' : '' }}">
            @if (!empty($data['heading']))
                <h2 class="text-3xl font-extrabold" style="color: var(--brand-ink)">{{ $data['heading'] }}</h2>
            @endif
            <div class="mt-4 prose prose-slate max-w-none">{!! $data['text'] ?? '' !!}</div>
        </div>
    </div>
</section>

@php
    $mod = \App\Models\Module::firstWhere('slug', $data['module'] ?? null);
    $limit = (int) ($data['limit'] ?? 6);
    $cols = (string) ($data['columns'] ?? 3);
    $colClass = ['2' => 'md:grid-cols-2', '3' => 'md:grid-cols-3', '4' => 'md:grid-cols-4'][$cols] ?? 'md:grid-cols-3';

    $items = $mod
        ? $mod->entries()->where('is_published', true)->orderBy('sort_order')->orderByDesc('created_at')->limit($limit)->get()
        : collect();

    $imgField = $mod?->fieldList()->firstWhere('type', 'image');
    $priceField = $mod?->fieldList()->firstWhere('type', 'number');
    $textField = $mod?->fieldList()->first(fn ($f) => in_array($f['type'] ?? '', ['text', 'textarea']));
@endphp

@if ($mod && $items->isNotEmpty())
    <section class="max-w-6xl mx-auto px-4 py-20 md:py-24">
        @if (!empty($data['heading']))
            <div class="max-w-2xl mb-12">
                <span class="eyebrow">{{ $mod->pluralLabel() }}</span>
                <h2 class="section-title mt-4">{{ $data['heading'] }}</h2>
            </div>
        @endif

        <div class="grid gap-5 {{ $colClass }}">
            @foreach ($items as $entry)
                @php $url = $mod->is_public ? route('module.show', [$mod->slug, $entry->slug]) : null; @endphp
                <div class="card overflow-hidden flex flex-col">
                    <{{ $url ? 'a' : 'div' }} @if($url) href="{{ $url }}" @endif class="group block flex-1">
                        @if ($imgField && ($img = data_get($entry->data, $imgField['key'])))
                            <div class="overflow-hidden">
                                <img src="{{ asset('storage/' . $img) }}" alt="{{ $entry->title }}" class="w-full h-52 object-cover transition-transform duration-500 group-hover:scale-105">
                            </div>
                        @endif
                        <div class="p-6">
                            <h3 class="font-display text-lg font-bold tracking-tight text-brandink group-hover:text-brand transition-colors">{{ $entry->title }}</h3>
                            @if ($priceField && ($price = data_get($entry->data, $priceField['key'])) !== null)
                                <div class="mt-1.5 font-display text-2xl font-extrabold text-brand">{{ number_format((float) $price, 2) }}</div>
                            @endif
                            @if ($textField && ($txt = data_get($entry->data, $textField['key'])))
                                <p class="mt-2 text-sm text-slate-500 leading-relaxed line-clamp-3">{{ \Illuminate\Support\Str::limit(strip_tags($txt), 110) }}</p>
                            @endif
                        </div>
                    </{{ $url ? 'a' : 'div' }}>
                    @if ($mod->type === 'tienda')
                        <form method="POST" action="{{ route('cart.add', $entry->id) }}" class="px-6 pb-6 mt-auto">
                            @csrf
                            <button type="submit" class="w-full py-2.5 rounded-xl bg-brand text-white font-semibold text-sm hover:opacity-90 transition">
                                Agregar al carrito
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        @if (!empty($data['show_link']) && $mod->is_public)
            <div class="text-center mt-12">
                <a href="{{ route('module.index', $mod->slug) }}" class="btn-outline">Ver todos</a>
            </div>
        @endif
    </section>
@endif

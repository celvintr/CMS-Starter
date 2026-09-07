@extends('layouts.app')

@section('title', $entry->title . ' — ' . $settings->site_name)

@section('content')
    <article class="max-w-4xl mx-auto px-4 py-14">
        <a href="{{ route('module.index', $module->slug) }}" class="text-sm text-brand hover:underline">
            &larr; {{ $module->pluralLabel() }}
        </a>
        <h1 class="mt-3 text-4xl font-extrabold" style="color: var(--brand-ink)">{{ $entry->title }}</h1>

        @if ($module->type === 'tienda')
            @php $soldOut = $entry->tracksStock() && (int) $entry->stock <= 0; @endphp
            @if ($soldOut)
                <div class="mt-6">
                    <span class="inline-block px-6 py-2.5 rounded-lg bg-slate-100 text-slate-400 font-semibold">Agotado</span>
                </div>
            @else
                <form method="POST" action="{{ route('cart.add', $entry->id) }}" class="mt-6 flex items-center gap-3 flex-wrap">
                    @csrf
                    <input type="number" name="qty" value="1" min="1" @if ($entry->tracksStock()) max="{{ $entry->stock }}" @endif
                           class="w-20 rounded-lg border border-slate-300 px-2 py-2 text-center">
                    <button type="submit" class="px-6 py-2.5 rounded-lg text-white font-semibold hover:opacity-90 transition" style="background: var(--brand)">
                        Agregar al carrito
                    </button>
                    @if ($entry->tracksStock() && (int) $entry->stock <= 5)
                        <span class="text-sm text-amber-600 font-medium">Solo quedan {{ $entry->stock }}</span>
                    @endif
                </form>
            @endif
        @endif

        <div class="mt-8 space-y-8">
            @foreach ($module->fieldList() as $field)
                @php $value = data_get($entry->data, $field['key']); @endphp
                @continue($value === null || $value === '' || $value === [])

                <div>
                    <div class="text-xs uppercase tracking-wide text-slate-400 mb-1">{{ $field['label'] ?? $field['key'] }}</div>

                    @switch($field['type'] ?? 'text')
                        @case('image')
                            <img src="{{ asset('storage/' . $value) }}" alt="" class="rounded-xl shadow-md max-h-96 object-cover">
                            @break

                        @case('gallery')
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach ((array) $value as $img)
                                    <img src="{{ asset('storage/' . $img) }}" alt="" class="w-full h-40 object-cover rounded-lg">
                                @endforeach
                            </div>
                            @break

                        @case('richtext')
                            <div class="prose prose-slate max-w-none">{!! $value !!}</div>
                            @break

                        @case('boolean')
                            <div class="text-slate-800">{{ $value ? 'Sí' : 'No' }}</div>
                            @break

                        @case('number')
                            <div class="text-2xl font-bold text-brand">{{ number_format((float) $value, 2) }}</div>
                            @break

                        @case('relation')
                            @php $rel = \App\Models\Entry::find($value); $relMod = $rel?->module; @endphp
                            @if ($rel && $relMod && $relMod->is_public)
                                <a href="{{ route('module.show', [$relMod->slug, $rel->slug]) }}" class="text-brand hover:underline font-medium">{{ $rel->title }}</a>
                            @elseif ($rel)
                                <div class="text-slate-800">{{ $rel->title }}</div>
                            @else
                                <div class="text-slate-400">—</div>
                            @endif
                            @break

                        @default
                            <div class="text-slate-800 whitespace-pre-line">{{ $value }}</div>
                    @endswitch
                </div>
            @endforeach
        </div>
    </article>
@endsection

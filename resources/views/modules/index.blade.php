@extends('layouts.app')

@section('title', $module->pluralLabel() . ' — ' . $settings->site_name)

@php
    $imgField = $module->fieldList()->firstWhere('type', 'image');
    $textField = $module->fieldList()->first(fn ($f) => in_array($f['type'] ?? '', ['text', 'textarea']));
    $priceField = $module->fieldList()->firstWhere('type', 'number');
@endphp

@section('content')
    <section class="max-w-6xl mx-auto px-4 pt-16 pb-24">
        <div class="mb-12">
            <span class="eyebrow">Catálogo</span>
            <h1 class="section-title mt-4">{{ $module->pluralLabel() }}</h1>
        </div>

        @if ($entries->isEmpty())
            <p class="text-slate-400">Todavía no hay registros publicados.</p>
        @else
            <div class="grid gap-5 md:grid-cols-3">
                @foreach ($entries as $entry)
                    <div class="card overflow-hidden flex flex-col">
                        <a href="{{ route('module.show', [$module->slug, $entry->slug]) }}" class="group block flex-1">
                            @if ($imgField && ($img = data_get($entry->data, $imgField['key'])))
                                <div class="overflow-hidden">
                                    <img src="{{ asset('storage/' . $img) }}" alt="{{ $entry->title }}" class="w-full h-56 object-cover transition-transform duration-500 group-hover:scale-105">
                                </div>
                            @else
                                <div class="w-full h-56 bg-slate-100"></div>
                            @endif
                            <div class="p-6">
                                <h2 class="font-display text-lg font-bold tracking-tight text-brandink group-hover:text-brand transition-colors">{{ $entry->title }}</h2>
                                @if ($priceField && ($price = data_get($entry->data, $priceField['key'])) !== null)
                                    <div class="mt-1.5 font-display text-2xl font-extrabold text-brand">{{ number_format((float) $price, 2) }}</div>
                                @endif
                                @if ($textField && ($txt = data_get($entry->data, $textField['key'])))
                                    <p class="mt-2 text-sm text-slate-500 leading-relaxed line-clamp-3">{{ \Illuminate\Support\Str::limit(strip_tags($txt), 120) }}</p>
                                @endif
                            </div>
                        </a>
                        @if ($module->type === 'tienda')
                            @include('partials.add-to-cart', ['entry' => $entry])
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-12">{{ $entries->links() }}</div>
        @endif
    </section>
@endsection

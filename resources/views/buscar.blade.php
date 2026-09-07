@extends('layouts.app')

@section('title', ($q ? 'Resultados: ' . $q : 'Buscar') . ' — ' . $settings->site_name)

@section('content')
    <section class="max-w-3xl mx-auto px-4 pt-16 pb-24">
        <span class="eyebrow">Búsqueda</span>
        <h1 class="section-title mt-4 mb-8">Buscar en el sitio</h1>

        <form action="{{ route('search') }}" method="GET" class="relative mb-10">
            <svg viewBox="0 0 24 24" class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m21 21-4.3-4.3"/></svg>
            <input type="search" name="q" value="{{ $q }}" placeholder="¿Qué estás buscando?" autofocus
                   class="w-full rounded-full border border-slate-300 pl-12 pr-4 py-3.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none">
        </form>

        @if ($q && mb_strlen($q) >= 2)
            @if ($results->isEmpty())
                <p class="text-slate-400 text-center py-12">No se encontraron resultados para <strong>“{{ $q }}”</strong>.</p>
            @else
                <p class="text-sm text-slate-400 mb-4">{{ $results->count() }} resultado(s) para “{{ $q }}”</p>
                <div class="space-y-2">
                    @foreach ($results as $r)
                        <a href="{{ $r['url'] }}" class="card block p-5">
                            <span class="text-xs font-semibold uppercase tracking-wider text-brand">{{ $r['type'] }}</span>
                            <div class="mt-1 font-display text-lg font-bold" style="color: var(--brand-ink)">{{ $r['title'] }}</div>
                            @if (!empty($r['snippet']))
                                <p class="mt-1 text-sm text-slate-500 line-clamp-2">{{ $r['snippet'] }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        @elseif ($q)
            <p class="text-slate-400 text-center py-12">Escribe al menos 2 letras.</p>
        @endif
    </section>
@endsection

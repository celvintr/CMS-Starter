@extends('layouts.app')

@section('title', 'Pago cancelado — ' . $settings->site_name)

@section('content')
    <section class="max-w-xl mx-auto px-4 py-24 text-center">
        <div class="mx-auto h-16 w-16 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center">
            <svg viewBox="0 0 24 24" class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </div>

        <h1 class="section-title mt-6">Pago cancelado</h1>
        <p class="mt-4 text-slate-500">No se realizó ningún cargo. Tu carrito sigue disponible.</p>

        <div class="mt-8 flex items-center justify-center gap-3">
            <a href="{{ route('cart.index') }}" class="btn-primary">Volver al carrito</a>
            <a href="{{ route('home') }}" class="btn-outline">Ir al inicio</a>
        </div>
    </section>
@endsection

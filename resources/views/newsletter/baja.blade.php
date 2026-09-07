@extends('layouts.app')

@section('title', 'Suscripción cancelada — ' . $settings->t('site_name'))

@section('content')
    <section class="max-w-xl mx-auto px-4 py-24 text-center">
        <div class="mx-auto mb-6 h-14 w-14 rounded-full bg-emerald-50 flex items-center justify-center">
            <svg viewBox="0 0 24 24" class="h-7 w-7 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h1 class="font-display text-3xl font-extrabold" style="color: var(--brand-ink)">Te diste de baja</h1>
        <p class="mt-3 text-slate-500">Ya no recibirás más correos de nuestro boletín. Si fue un error, puedes volver a suscribirte cuando quieras.</p>
        <a href="{{ route('home') }}" class="mt-6 inline-block text-brand font-semibold hover:underline">Volver al inicio</a>
    </section>
@endsection

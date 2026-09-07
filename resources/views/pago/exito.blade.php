@extends('layouts.app')

@section('title', 'Pago recibido — ' . $settings->site_name)

@section('content')
    <section class="max-w-xl mx-auto px-4 py-24 text-center">
        <div class="mx-auto h-16 w-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
            <svg viewBox="0 0 24 24" class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </div>

        <h1 class="section-title mt-6">¡Gracias por tu compra!</h1>

        @if ($order)
            <p class="mt-4 text-slate-500">
                Tu orden <strong style="color: var(--brand-ink)">{{ $order->reference }}</strong> por
                <strong>{{ number_format($order->total, 2) }} {{ strtoupper($order->currency) }}</strong> quedó registrada.
            </p>
            @if ($order->isPaid())
                <p class="mt-2 font-semibold text-emerald-600">Pago confirmado.</p>
            @else
                <p class="mt-2 text-sm text-slate-400">Estamos confirmando tu pago; recibirás la confirmación en breve.</p>
            @endif
        @endif

        <div class="mt-8">
            <a href="{{ route('home') }}" class="btn-primary">Volver al inicio</a>
        </div>
    </section>
@endsection

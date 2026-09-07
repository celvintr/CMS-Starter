@extends('layouts.app')

@section('title', 'Carrito — ' . $settings->site_name)

@section('content')
    <section class="max-w-4xl mx-auto px-4 py-14">
        <h1 class="text-4xl font-extrabold mb-8" style="color: var(--brand-ink)">Tu carrito</h1>

        @if (empty($items))
            <div class="rounded-2xl border border-slate-100 bg-white p-10 text-center text-slate-400">
                Tu carrito está vacío.
                <div class="mt-4">
                    <a href="{{ route('home') }}" class="text-brand font-semibold hover:underline">Seguir viendo productos</a>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('cart.update') }}">
                @csrf
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm divide-y">
                    @foreach ($items as $it)
                        <div class="flex items-center gap-4 p-4">
                            @if ($it['img'])
                                <img src="{{ asset('storage/' . $it['img']) }}" alt="" class="h-16 w-16 rounded-lg object-cover">
                            @else
                                <div class="h-16 w-16 rounded-lg bg-slate-100"></div>
                            @endif
                            <div class="flex-1">
                                <div class="font-semibold" style="color: var(--brand-ink)">{{ $it['title'] }}</div>
                                <div class="text-sm text-slate-500">{{ number_format($it['price'], 2) }} c/u</div>
                            </div>
                            <input type="number" name="qty[{{ $it['id'] }}]" value="{{ $it['qty'] }}" min="1"
                                   class="w-20 rounded-lg border border-slate-300 px-2 py-1 text-center">
                            <div class="w-24 text-right font-bold" style="color: var(--brand-ink)">{{ number_format($it['subtotal'], 2) }}</div>
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="mt-4 text-sm text-slate-500 hover:text-brand">Actualizar cantidades</button>
            </form>

            {{-- Cupón de descuento --}}
            <div class="mt-4 bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                @if ($coupon)
                    <div class="flex items-center justify-between gap-3">
                        <div class="text-sm">
                            <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-700">
                                <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Cupón <strong>{{ $coupon->code }}</strong> aplicado
                            </span>
                        </div>
                        <form method="POST" action="{{ route('cart.coupon.remove') }}">
                            @csrf
                            <button type="submit" class="text-sm text-slate-400 hover:text-red-600">Quitar</button>
                        </form>
                    </div>
                @else
                    <form method="POST" action="{{ route('cart.coupon.apply') }}" class="flex items-center gap-3">
                        @csrf
                        <input type="text" name="coupon" placeholder="¿Tienes un cupón?" value="{{ old('coupon') }}"
                               class="flex-1 rounded-xl border border-slate-300 px-4 py-2.5 uppercase tracking-wide focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none">
                        <button type="submit" class="rounded-xl border border-slate-300 px-5 py-2.5 font-semibold text-slate-700 hover:border-brand hover:text-brand transition-colors">Aplicar</button>
                    </form>
                    @if (session('coupon_error'))
                        <p class="mt-2 text-sm text-red-600">{{ session('coupon_error') }}</p>
                    @endif
                @endif
            </div>

            {{-- Totales --}}
            <div class="mt-4 bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-2">
                <div class="flex justify-between text-slate-500">
                    <span>Subtotal</span>
                    <span>{{ number_format($subtotal, 2) }}</span>
                </div>
                @if ($discount > 0)
                    <div class="flex justify-between text-emerald-700">
                        <span>Descuento{{ $coupon ? ' · ' . $coupon->code : '' }}</span>
                        <span>−{{ number_format($discount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center pt-2 border-t border-slate-100 text-2xl font-extrabold" style="color: var(--brand-ink)">
                    <span>Total</span>
                    <span>{{ number_format($total, 2) }}</span>
                </div>
            </div>

            @if ($settings->stripeReady() || $settings->paypalReady())
                <div class="mt-8 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h2 class="text-lg font-bold mb-4" style="color: var(--brand-ink)">Pagar en línea</h2>
                    <form method="POST" action="{{ route('pago.checkout') }}" class="space-y-4">
                        @csrf
                        @if ($errors->any())
                            <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5">{{ $errors->first() }}</div>
                        @endif
                        <div class="grid gap-4 md:grid-cols-2">
                            <input type="text" name="nombre" placeholder="Tu nombre *" required value="{{ old('nombre') }}"
                                   class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none">
                            <input type="email" name="email" placeholder="Tu correo *" required value="{{ old('email') }}"
                                   class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none">
                        </div>
                        <input type="text" name="telefono" placeholder="Teléfono (opcional)" value="{{ old('telefono') }}"
                               class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none">

                        <div class="grid gap-3 @if ($settings->stripeReady() && $settings->paypalReady()) md:grid-cols-2 @endif">
                            @if ($settings->stripeReady())
                                <button type="submit" formaction="{{ route('pago.checkout') }}" class="btn-primary w-full">
                                    Tarjeta · {{ number_format($total, 2) }} {{ strtoupper($settings->currency ?: 'USD') }}
                                </button>
                            @endif
                            @if ($settings->paypalReady())
                                <button type="submit" formaction="{{ route('paypal.checkout') }}"
                                        class="w-full inline-flex items-center justify-center rounded-full px-7 py-3.5 font-bold transition hover:opacity-90"
                                        style="background:#ffc439; color:#003087;">
                                    Pagar con PayPal
                                </button>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 text-center">Pago seguro. Serás redirigido para completar la compra.</p>
                    </form>
                </div>
            @endif

            <div class="mt-8 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h2 class="text-lg font-bold mb-4" style="color: var(--brand-ink)">{{ $settings->stripeReady() ? 'O finalizar por WhatsApp' : 'Finalizar pedido' }}</h2>
                <form method="POST" action="{{ route('cart.checkout') }}" class="space-y-4">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <input type="text" name="nombre" placeholder="Tu nombre"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-brand focus:border-brand outline-none">
                        <input type="text" name="nota" placeholder="Nota (opcional)"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-brand focus:border-brand outline-none">
                    </div>
                    <button type="submit"
                            class="w-full py-3 rounded-lg bg-green-500 text-white font-semibold hover:bg-green-600 transition flex items-center justify-center gap-2">
                        <svg viewBox="0 0 24 24" class="h-5 w-5" fill="currentColor"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.945C.16 5.335 5.495 0 12.05 0a11.817 11.817 0 018.413 3.488 11.824 11.824 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.599 5.336l-.999 3.648 3.9-1.283z"/></svg>
                        Enviar pedido por WhatsApp
                    </button>
                    <p class="text-xs text-slate-400 text-center">Se abrirá WhatsApp con el detalle de tu pedido listo para enviar.</p>
                </form>
            </div>
        @endif
    </section>
@endsection

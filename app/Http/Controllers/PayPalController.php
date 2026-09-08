<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Support\Notifier;
use App\Support\PayPal;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayPalController extends Controller
{
    public function checkout(Request $request)
    {
        $settings = SiteSetting::current();

        if (! $settings->paypalReady()) {
            return back()->with('sent', 'PayPal no está disponible por el momento.');
        }

        $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:40'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);

        // Montos calculados en el servidor desde el carrito.
        $summary = (new CartController())->summary();
        $items = $summary['items'];
        $total = $summary['total'];

        if (empty($items) || $total <= 0) {
            return redirect()->route('cart.index');
        }

        $currency = strtoupper($settings->currency ?: 'USD');

        $order = Order::create([
            'reference' => 'ORD-' . strtoupper(Str::random(8)),
            'provider' => 'paypal',
            'customer_name' => $request->input('nombre'),
            'customer_email' => $request->input('email'),
            'customer_phone' => $request->input('telefono'),
            'items' => $items,
            'total' => $total,
            'currency' => $currency,
            'coupon_code' => $summary['coupon']?->code,
            'discount' => $summary['discount'],
            'shipping' => $summary['shipping'],
            'shipping_address' => $request->input('direccion'),
            'status' => 'pending',
        ]);

        try {
            $ppOrder = PayPal::createOrder(
                (float) $total,
                $currency,
                $order->reference,
                route('paypal.capture', ['ref' => $order->reference]),
                route('paypal.cancel', ['ref' => $order->reference]),
            );
            $link = PayPal::approveLink($ppOrder);
        } catch (\Throwable $e) {
            $order->update(['status' => 'canceled']);

            return back()->with('sent', 'No se pudo iniciar el pago con PayPal. Intenta de nuevo o usa otro método.');
        }

        if (! $link) {
            $order->update(['status' => 'canceled']);

            return back()->with('sent', 'No se pudo iniciar el pago con PayPal.');
        }

        $order->update(['paypal_order_id' => $ppOrder['id'] ?? null]);

        return redirect()->away($link);
    }

    public function capture(Request $request)
    {
        $order = Order::where('reference', $request->query('ref'))->first();
        $paypalOrderId = $request->query('token'); // PayPal devuelve el id de la orden en ?token=

        if (! $order || ! $paypalOrderId) {
            return redirect()->route('cart.index');
        }

        try {
            $result = PayPal::captureOrder($paypalOrderId);
        } catch (\Throwable $e) {
            return view('pago.cancelado', compact('order'));
        }

        if (($result['status'] ?? null) === 'COMPLETED') {
            if ($order->status !== 'paid') {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'paypal_capture_id' => data_get($result, 'purchase_units.0.payments.captures.0.id'),
                ]);

                Coupon::redeem($order->coupon_code);
                $order->reduceStock();
                Notifier::order($order);
            }

            session()->forget(['cart', 'coupon']);

            return view('pago.exito', compact('order'));
        }

        return view('pago.cancelado', compact('order'));
    }

    public function cancel(Request $request)
    {
        $order = Order::where('reference', $request->query('ref'))->first();

        if ($order && $order->status === 'pending') {
            $order->update(['status' => 'canceled']);
        }

        return view('pago.cancelado', compact('order'));
    }
}

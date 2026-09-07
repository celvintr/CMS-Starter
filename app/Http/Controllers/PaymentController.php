<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\SiteSetting;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stripe\StripeClient;
use Stripe\Webhook;

class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        $settings = SiteSetting::current();

        if (! $settings->stripeReady()) {
            return back()->with('sent', 'Los pagos con tarjeta no están disponibles por el momento.');
        }

        $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:40'],
        ]);

        // Los montos se calculan en el servidor desde el carrito (nunca del cliente).
        $summary = (new CartController())->summary();
        $items = $summary['items'];
        $discount = $summary['discount'];
        $total = $summary['total'];

        if (empty($items) || $total <= 0) {
            return redirect()->route('cart.index');
        }

        $currency = strtolower($settings->currency ?: 'usd');

        $order = Order::create([
            'reference' => 'ORD-' . strtoupper(Str::random(8)),
            'provider' => 'stripe',
            'customer_name' => $request->input('nombre'),
            'customer_email' => $request->input('email'),
            'customer_phone' => $request->input('telefono'),
            'items' => $items,
            'total' => $total,
            'currency' => $currency,
            'coupon_code' => $summary['coupon']?->code,
            'discount' => $discount,
            'status' => 'pending',
        ]);

        $lineItems = array_map(fn ($it) => [
            'price_data' => [
                'currency' => $currency,
                'product_data' => ['name' => $it['title']],
                'unit_amount' => (int) round(((float) $it['price']) * 100),
            ],
            'quantity' => (int) $it['qty'],
        ], $items);

        try {
            $stripe = new StripeClient($settings->stripe_secret_key);

            $params = [
                'mode' => 'payment',
                'line_items' => $lineItems,
                'customer_email' => $order->customer_email,
                'client_reference_id' => $order->reference,
                'metadata' => ['order_reference' => $order->reference],
                'success_url' => route('pago.exito', ['ref' => $order->reference]) . '&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('pago.cancelado', ['ref' => $order->reference]),
            ];

            // El descuento del cupón se aplica como cupón puntual de Stripe.
            if ($discount > 0) {
                $stripeCoupon = $stripe->coupons->create([
                    'amount_off' => (int) round($discount * 100),
                    'currency' => $currency,
                    'duration' => 'once',
                    'name' => 'Descuento' . ($summary['coupon'] ? ' ' . $summary['coupon']->code : ''),
                ]);
                $params['discounts'] = [['coupon' => $stripeCoupon->id]];
            }

            $session = $stripe->checkout->sessions->create($params);
        } catch (\Throwable $e) {
            $order->update(['status' => 'canceled']);

            return back()->with('sent', 'No se pudo iniciar el pago. Intenta de nuevo o usa WhatsApp.');
        }

        $order->update(['stripe_session_id' => $session->id]);

        return redirect()->away($session->url);
    }

    public function success(Request $request)
    {
        $order = Order::where('reference', $request->query('ref'))->first();

        // El pago se confirma por webhook; al volver, limpiamos el carrito y el cupón.
        session()->forget(['cart', 'coupon']);

        return view('pago.exito', compact('order'));
    }

    public function cancel(Request $request)
    {
        $order = Order::where('reference', $request->query('ref'))->first();

        if ($order && $order->status === 'pending') {
            $order->update(['status' => 'canceled']);
        }

        return view('pago.cancelado', compact('order'));
    }

    /**
     * Webhook de Stripe: confirma el pago (con verificación de firma).
     */
    public function webhook(Request $request)
    {
        $settings = SiteSetting::current();
        $payload = $request->getContent();

        try {
            if ($secret = $settings->stripe_webhook_secret) {
                $event = Webhook::constructEvent($payload, $request->header('Stripe-Signature'), $secret);
            } else {
                // Sin secreto configurado no se puede verificar la firma.
                $event = json_decode($payload);
            }
        } catch (\Throwable $e) {
            return response('Firma inválida', 400);
        }

        $type = is_object($event) ? ($event->type ?? null) : null;

        if ($type === 'checkout.session.completed') {
            $object = $event->data->object;
            $reference = $object->metadata->order_reference ?? ($object->client_reference_id ?? null);

            $order = $reference ? Order::where('reference', $reference)->first() : null;

            if ($order && $order->status !== 'paid') {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'stripe_payment_intent' => $object->payment_intent ?? null,
                ]);

                Coupon::redeem($order->coupon_code);
                Notifier::order($order);
            }
        }

        return response('ok', 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Entry;
use App\Models\SiteSetting;
use App\Support\Features;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request, int $entryId)
    {
        $entry = Entry::findOrFail($entryId);
        $qty = max(1, (int) $request->input('qty', 1));

        $cart = session('cart', []);
        $current = (int) ($cart[$entry->id] ?? 0);
        $desired = $current + $qty;

        // Respeta el stock cuando el producto lo controla.
        if ($entry->tracksStock()) {
            if ((int) $entry->stock <= 0) {
                return back()->with('sent', 'Este producto está agotado.');
            }

            $desired = min($desired, (int) $entry->stock);

            if ($desired <= $current) {
                return back()->with('sent', 'No hay más unidades disponibles de este producto.');
            }
        }

        $cart[$entry->id] = $desired;
        session(['cart' => $cart]);

        return back()->with('sent', 'Producto agregado al carrito.');
    }

    public function index()
    {
        return view('cart.index', $this->summary());
    }

    /**
     * Aplica un cupón al carrito (lo guarda en sesión si es válido).
     */
    public function applyCoupon(Request $request)
    {
        if (! Features::enabled('tienda')) {
            return back();
        }

        $code = trim((string) $request->input('coupon'));
        [$items, $subtotal] = $this->itemsFrom(session('cart', []));
        $moduleIds = collect($items)->pluck('module_id')->filter()->unique()->values()->all();

        $coupon = Coupon::findByCode($code);

        if (! $coupon) {
            return back()->with('coupon_error', 'Cupón no encontrado.');
        }

        [$ok, $reason] = $coupon->validateFor($subtotal, $moduleIds);

        if (! $ok) {
            return back()->with('coupon_error', $reason);
        }

        session(['coupon' => $coupon->code]);

        return back()->with('sent', 'Cupón aplicado.');
    }

    /**
     * Quita el cupón aplicado.
     */
    public function removeCoupon()
    {
        session()->forget('coupon');

        return back();
    }

    public function update(Request $request)
    {
        $requested = collect((array) $request->input('qty', []))
            ->mapWithKeys(fn ($qty, $id) => [(int) $id => (int) $qty])
            ->filter(fn ($qty) => $qty > 0);

        // Capamos cada cantidad al stock disponible del producto.
        $entries = Entry::whereIn('id', $requested->keys())->get()->keyBy('id');

        $cart = [];
        foreach ($requested as $id => $qty) {
            $entry = $entries->get($id);
            $qty = $entry ? max($entry->clampQuantity($qty), 0) : $qty;

            if ($qty > 0) {
                $cart[$id] = $qty;
            }
        }
        session(['cart' => $cart]);

        return back();
    }

    public function remove(int $entryId)
    {
        $cart = session('cart', []);
        unset($cart[$entryId]);
        session(['cart' => $cart]);

        return back();
    }

    public function checkout(Request $request)
    {
        $summary = $this->summary();

        if (empty($summary['items'])) {
            return back();
        }

        $url = $this->buildWhatsappUrl($summary, $request->input('nombre'), $request->input('nota'));

        return redirect()->away($url);
    }

    /**
     * Resumen del carrito con cupón aplicado: líneas, subtotal, descuento y total.
     * El descuento y el cupón se recalculan (y validan) en cada llamada, así que
     * un cupón que dejó de ser válido simplemente no se aplica.
     *
     * @return array{items: array, subtotal: float, discount: float, total: float, coupon: ?\App\Models\Coupon, couponError: ?string}
     */
    public function summary(): array
    {
        [$items, $subtotal] = $this->itemsFrom(session('cart', []));

        $coupon = null;
        $discount = 0.0;
        $couponError = null;

        if (Features::enabled('tienda') && ($code = session('coupon')) && $subtotal > 0) {
            $found = Coupon::findByCode($code);
            $moduleIds = collect($items)->pluck('module_id')->filter()->unique()->values()->all();

            if ($found) {
                [$ok, $reason] = $found->validateFor($subtotal, $moduleIds);

                if ($ok) {
                    $coupon = $found;
                    $discount = $found->discountOn($subtotal);
                } else {
                    $couponError = $reason;
                }
            } else {
                $couponError = 'El cupón ya no está disponible.';
            }
        }

        $total = round(max(0, $subtotal - $discount), 2);

        return compact('items', 'subtotal', 'discount', 'total', 'coupon', 'couponError');
    }

    /**
     * Convierte el carrito (id => cantidad) en líneas con precio y total.
     */
    public function itemsFrom(array $cart): array
    {
        $items = [];
        $total = 0.0;

        if (! empty($cart)) {
            $entries = Entry::with('module')->whereIn('id', array_keys($cart))->get()->keyBy('id');

            foreach ($cart as $id => $qty) {
                $entry = $entries->get($id);
                if (! $entry) {
                    continue;
                }

                $priceField = optional($entry->module)->fieldList()->firstWhere('type', 'number');
                $imgField = optional($entry->module)->fieldList()->firstWhere('type', 'image');

                $price = $priceField ? (float) data_get($entry->data, $priceField['key'], 0) : 0.0;
                $subtotal = $price * $qty;
                $total += $subtotal;

                $items[] = [
                    'id' => $id,
                    'module_id' => $entry->module_id,
                    'title' => $entry->title,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                    'img' => $imgField ? data_get($entry->data, $imgField['key']) : null,
                ];
            }
        }

        return [$items, $total];
    }

    /**
     * Arma el enlace de WhatsApp con el detalle del pedido (incluye descuento).
     */
    public function buildWhatsappUrl(array $summary, ?string $nombre = null, ?string $nota = null): string
    {
        $settings = SiteSetting::current();
        $number = preg_replace('/\D+/', '', $settings->whatsapp ?? '');

        $lines = ['*Nuevo pedido* — ' . $settings->site_name, ''];
        if ($nombre) {
            $lines[] = 'Cliente: ' . $nombre;
            $lines[] = '';
        }
        foreach ($summary['items'] as $it) {
            $lines[] = "• {$it['qty']} x {$it['title']} = " . number_format($it['subtotal'], 2);
        }
        $lines[] = '';
        if (($summary['discount'] ?? 0) > 0) {
            $lines[] = 'Subtotal: ' . number_format($summary['subtotal'], 2);
            $code = $summary['coupon'] ? $summary['coupon']->code : 'cupón';
            $lines[] = "Descuento ({$code}): -" . number_format($summary['discount'], 2);
        }
        $lines[] = '*Total: ' . number_format($summary['total'], 2) . '*';
        if ($nota) {
            $lines[] = '';
            $lines[] = 'Nota: ' . $nota;
        }

        return "https://wa.me/{$number}?text=" . rawurlencode(implode("\n", $lines));
    }
}

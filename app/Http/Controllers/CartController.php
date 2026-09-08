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

        // Si el producto tiene variantes, hay que elegir una válida.
        $variantIndex = null;
        if ($entry->hasVariants()) {
            $variantIndex = $request->filled('variant') ? (int) $request->input('variant') : -1;
            if ($entry->variant($variantIndex) === null) {
                return back()->with('sent', 'Elige una opción del producto.');
            }
        }

        $key = static::cartKey($entry->id, $variantIndex);
        $cart = session('cart', []);
        $current = (int) ($cart[$key] ?? 0);
        $desired = $current + $qty;

        // Respeta el stock (de la variante si aplica, si no del producto).
        if ($variantIndex !== null) {
            if ($entry->variantTracksStock($variantIndex)) {
                if ((int) $entry->variantStock($variantIndex) <= 0) {
                    return back()->with('sent', 'Esta opción está agotada.');
                }

                $desired = min($desired, (int) $entry->variantStock($variantIndex));

                if ($desired <= $current) {
                    return back()->with('sent', 'No hay más unidades de esa opción.');
                }
            }
        } elseif ($entry->tracksStock()) {
            if ((int) $entry->stock <= 0) {
                return back()->with('sent', 'Este producto está agotado.');
            }

            $desired = min($desired, (int) $entry->stock);

            if ($desired <= $current) {
                return back()->with('sent', 'No hay más unidades disponibles de este producto.');
            }
        }

        $cart[$key] = $desired;
        session(['cart' => $cart]);

        return back()->with('sent', 'Producto agregado al carrito.');
    }

    /**
     * Clave de línea del carrito: "id" para producto simple, "id:variante" con opción.
     */
    public static function cartKey(int $entryId, ?int $variantIndex): string
    {
        return $variantIndex === null ? (string) $entryId : $entryId . ':' . $variantIndex;
    }

    /**
     * Descompone una clave del carrito en [id, índice de variante|null].
     *
     * @return array{0:int,1:?int}
     */
    public static function parseKey(int|string $key): array
    {
        $key = (string) $key;

        if (str_contains($key, ':')) {
            [$id, $variant] = explode(':', $key, 2);

            return [(int) $id, is_numeric($variant) ? (int) $variant : null];
        }

        return [(int) $key, null];
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
            ->mapWithKeys(fn ($qty, $key) => [(string) $key => (int) $qty])
            ->filter(fn ($qty) => $qty > 0);

        $ids = $requested->keys()->map(fn ($key) => static::parseKey($key)[0])->unique()->all();
        $entries = Entry::whereIn('id', $ids)->get()->keyBy('id');

        // Capamos cada cantidad al stock disponible (de la variante o del producto).
        $cart = [];
        foreach ($requested as $key => $qty) {
            [$id, $variantIndex] = static::parseKey($key);
            $entry = $entries->get($id);

            if ($entry) {
                if ($variantIndex !== null && $entry->variantTracksStock($variantIndex)) {
                    $qty = min($qty, max(0, (int) $entry->variantStock($variantIndex)));
                } elseif ($variantIndex === null && $entry->tracksStock()) {
                    $qty = min($qty, max(0, (int) $entry->stock));
                }
            }

            if ($qty > 0) {
                $cart[$key] = $qty;
            }
        }
        session(['cart' => $cart]);

        return back();
    }

    public function remove(Request $request)
    {
        $cart = session('cart', []);
        unset($cart[(string) $request->input('key')]);
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
     * Convierte el carrito (clave => cantidad) en líneas con precio y total.
     * La clave puede traer variante ("id:variante").
     */
    public function itemsFrom(array $cart): array
    {
        $items = [];
        $total = 0.0;

        if (! empty($cart)) {
            $ids = collect(array_keys($cart))->map(fn ($key) => static::parseKey($key)[0])->unique()->all();
            $entries = Entry::with('module')->whereIn('id', $ids)->get()->keyBy('id');

            foreach ($cart as $key => $qty) {
                [$id, $variantIndex] = static::parseKey($key);
                $entry = $entries->get($id);
                if (! $entry) {
                    continue;
                }

                $imgField = optional($entry->module)->fieldList()->firstWhere('type', 'image');

                // Precio: de la variante si la línea la trae; si no, del campo número del módulo.
                if ($variantIndex !== null && ($variant = $entry->variant($variantIndex))) {
                    $price = $variant['price'];
                    $variantLabel = $variant['label'];
                } else {
                    $variantIndex = null;
                    $variantLabel = null;
                    $priceField = optional($entry->module)->fieldList()->firstWhere('type', 'number');
                    $price = $priceField ? (float) data_get($entry->data, $priceField['key'], 0) : 0.0;
                }

                $subtotal = $price * $qty;
                $total += $subtotal;

                $items[] = [
                    'key' => (string) $key,
                    'id' => $id,
                    'module_id' => $entry->module_id,
                    'variant' => $variantLabel,
                    'variant_index' => $variantIndex,
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
            $name = $it['title'] . (! empty($it['variant']) ? ' (' . $it['variant'] . ')' : '');
            $lines[] = "• {$it['qty']} x {$name} = " . number_format($it['subtotal'], 2);
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

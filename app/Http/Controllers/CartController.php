<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request, int $entryId)
    {
        $entry = Entry::findOrFail($entryId);
        $qty = max(1, (int) $request->input('qty', 1));

        $cart = session('cart', []);
        $cart[$entry->id] = ($cart[$entry->id] ?? 0) + $qty;
        session(['cart' => $cart]);

        return back()->with('sent', 'Producto agregado al carrito.');
    }

    public function index()
    {
        [$items, $total] = $this->itemsFrom(session('cart', []));

        return view('cart.index', compact('items', 'total'));
    }

    public function update(Request $request)
    {
        $cart = [];
        foreach ((array) $request->input('qty', []) as $id => $qty) {
            $qty = (int) $qty;
            if ($qty > 0) {
                $cart[(int) $id] = $qty;
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
        [$items, $total] = $this->itemsFrom(session('cart', []));

        if (empty($items)) {
            return back();
        }

        $url = $this->buildWhatsappUrl($items, $total, $request->input('nombre'), $request->input('nota'));

        return redirect()->away($url);
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
     * Arma el enlace de WhatsApp con el detalle del pedido.
     */
    public function buildWhatsappUrl(array $items, float $total, ?string $nombre = null, ?string $nota = null): string
    {
        $settings = SiteSetting::current();
        $number = preg_replace('/\D+/', '', $settings->whatsapp ?? '');

        $lines = ['*Nuevo pedido* — ' . $settings->site_name, ''];
        if ($nombre) {
            $lines[] = 'Cliente: ' . $nombre;
            $lines[] = '';
        }
        foreach ($items as $it) {
            $lines[] = "• {$it['qty']} x {$it['title']} = " . number_format($it['subtotal'], 2);
        }
        $lines[] = '';
        $lines[] = '*Total: ' . number_format($total, 2) . '*';
        if ($nota) {
            $lines[] = '';
            $lines[] = 'Nota: ' . $nota;
        }

        return "https://wa.me/{$number}?text=" . rawurlencode(implode("\n", $lines));
    }
}

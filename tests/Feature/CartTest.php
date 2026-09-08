<?php

namespace Tests\Feature;

use App\Http\Controllers\CartController;
use App\Models\Entry;
use App\Models\Module;
use App\Models\Order;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function shopEntry(array $overrides = []): Entry
    {
        $module = Module::create([
            'name' => 'Productos', 'slug' => 'productos', 'type' => 'tienda', 'is_public' => true,
            'fields' => [['key' => 'precio', 'label' => 'Precio', 'type' => 'number']],
        ]);

        return Entry::create(array_merge([
            'module_id' => $module->id, 'title' => 'Camisa', 'slug' => 'camisa',
            'is_published' => true, 'data' => ['precio' => 100],
        ], $overrides));
    }

    public function test_add_respects_simple_stock(): void
    {
        $entry = $this->shopEntry(['stock' => 3]);

        $this->post(route('cart.add', $entry->id), ['qty' => 5]);

        $this->assertSame(3, session('cart')[$entry->id]); // capado al stock
    }

    public function test_sold_out_product_is_not_added(): void
    {
        $entry = $this->shopEntry(['stock' => 0]);

        $this->post(route('cart.add', $entry->id), ['qty' => 1]);

        $this->assertArrayNotHasKey($entry->id, (array) session('cart'));
    }

    public function test_variant_add_uses_variant_price_and_stock(): void
    {
        $entry = $this->shopEntry(['variants' => [
            ['label' => 'M', 'price' => 250, 'stock' => 3],
            ['label' => 'XL', 'price' => 280, 'stock' => 0],
        ]]);

        // Variante válida, capada a su stock.
        $this->post(route('cart.add', $entry->id), ['variant' => 0, 'qty' => 9]);
        $this->assertSame(3, session('cart')[$entry->id . ':0']);

        // Variante agotada: no se agrega.
        $this->post(route('cart.add', $entry->id), ['variant' => 1, 'qty' => 1]);
        $this->assertArrayNotHasKey($entry->id . ':1', (array) session('cart'));
    }

    public function test_shipping_added_to_total_with_free_threshold(): void
    {
        $settings = SiteSetting::current();

        // Envío apagado: 0.
        $this->assertSame(0.0, $settings->shippingFor(100));

        // Envío plano de 50, gratis desde 500.
        $settings->update(['shipping_enabled' => true, 'shipping_cost' => 50, 'shipping_free_from' => 500]);
        $settings->refresh();
        $this->assertSame(50.0, $settings->shippingFor(100));  // no alcanza el umbral
        $this->assertSame(0.0, $settings->shippingFor(500));   // envío gratis

        // El resumen del carrito suma el envío al total.
        $entry = $this->shopEntry(['data' => ['precio' => 100]]);
        session(['cart' => [$entry->id => 2]]); // subtotal 200
        $summary = (new CartController())->summary();
        $this->assertSame(200.0, $summary['subtotal']);
        $this->assertSame(50.0, $summary['shipping']);
        $this->assertSame(250.0, $summary['total']);
    }

    public function test_reduce_stock_from_paid_order(): void
    {
        $entry = $this->shopEntry(['variants' => [['label' => 'M', 'price' => 250, 'stock' => 3]]]);

        $order = Order::create([
            'reference' => 'T-1', 'provider' => 'stripe', 'customer_name' => 'X',
            'items' => [['id' => $entry->id, 'variant_index' => 0, 'qty' => 2]],
            'total' => 500, 'currency' => 'usd', 'status' => 'paid',
        ]);

        $order->reduceStock();

        $this->assertSame(1, $entry->fresh()->variantStock(0));
    }
}

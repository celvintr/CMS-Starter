<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'reference', 'provider', 'customer_name', 'customer_email', 'customer_phone',
        'items', 'total', 'currency', 'status', 'coupon_code', 'discount', 'shipping', 'shipping_address',
        'stripe_session_id', 'stripe_payment_intent',
        'paypal_order_id', 'paypal_capture_id', 'paid_at',
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            'pending' => 'Pendiente',
            'paid' => 'Pagada',
            'shipped' => 'Enviada',
            'delivered' => 'Entregada',
            'canceled' => 'Cancelada',
        ];
    }

    public function statusLabel(): string
    {
        return static::statuses()[$this->status] ?? $this->status;
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Descuenta del inventario las unidades de esta orden (solo productos con
     * control de stock). Se llama una vez al confirmarse el pago.
     */
    public function reduceStock(): void
    {
        foreach ((array) $this->items as $item) {
            $id = $item['id'] ?? null;
            $qty = (int) ($item['qty'] ?? 0);

            if (! $id || $qty <= 0) {
                continue;
            }

            $entry = Entry::find($id);
            if (! $entry) {
                continue;
            }

            $variantIndex = $item['variant_index'] ?? null;

            if ($variantIndex !== null) {
                // Descuenta el stock de la variante comprada (si la controla).
                $variants = $entry->variants ?? [];
                $stock = $variants[$variantIndex]['stock'] ?? null;

                if (isset($variants[$variantIndex]) && $stock !== null && $stock !== '') {
                    $variants[$variantIndex]['stock'] = max(0, (int) $stock - $qty);
                    $entry->update(['variants' => $variants]);
                }
            } elseif ($entry->tracksStock()) {
                $entry->update(['stock' => max(0, (int) $entry->stock - $qty)]);
            }
        }
    }
}

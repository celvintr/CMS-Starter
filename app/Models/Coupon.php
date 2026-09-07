<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    protected $fillable = [
        'module_id', 'code', 'type', 'value', 'active', 'expires_at', 'min_total', 'max_uses', 'uses',
    ];

    protected $casts = [
        'active' => 'boolean',
        'expires_at' => 'datetime',
        'value' => 'decimal:2',
        'min_total' => 'decimal:2',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Busca un cupón por código, sin distinguir mayúsculas ni espacios.
     */
    public static function findByCode(?string $code): ?self
    {
        $code = trim((string) $code);

        if ($code === '') {
            return null;
        }

        return static::whereRaw('LOWER(code) = ?', [mb_strtolower($code)])->first();
    }

    /**
     * ¿El cupón aplica a un carrito con este subtotal y estos módulos?
     *
     * @param  array<int, int>  $moduleIds  IDs de los módulos presentes en el carrito
     * @return array{0: bool, 1: ?string}   [ok, motivo del rechazo]
     */
    public function validateFor(float $subtotal, array $moduleIds): array
    {
        if (! $this->active) {
            return [false, 'Este cupón no está activo.'];
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return [false, 'Este cupón ya venció.'];
        }

        if ($this->max_uses !== null && $this->uses >= $this->max_uses) {
            return [false, 'Este cupón alcanzó su límite de usos.'];
        }

        if ($this->min_total !== null && $subtotal < (float) $this->min_total) {
            return [false, 'Requiere una compra mínima de ' . number_format((float) $this->min_total, 2) . '.'];
        }

        if ($this->module_id !== null && ! in_array($this->module_id, $moduleIds, true)) {
            return [false, 'Este cupón no aplica a los productos de tu carrito.'];
        }

        return [true, null];
    }

    /**
     * Descuento en dinero para un subtotal dado (nunca mayor que el subtotal).
     */
    public function discountOn(float $subtotal): float
    {
        $discount = $this->type === 'percent'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        return round(max(0, min($discount, $subtotal)), 2);
    }

    /**
     * Registra un uso del cupón con el código dado (si existe). Se llama al
     * confirmarse el pago de una orden.
     */
    public static function redeem(?string $code): void
    {
        if ($coupon = static::findByCode($code)) {
            $coupon->increment('uses');
        }
    }
}

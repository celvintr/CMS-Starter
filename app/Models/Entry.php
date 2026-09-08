<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entry extends Model
{
    use Publishable;

    protected $fillable = [
        'module_id', 'title', 'slug', 'data', 'stock', 'variants', 'is_published', 'published_at', 'sort_order',
    ];

    protected $casts = [
        'data' => 'array',
        'stock' => 'integer',
        'variants' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * ¿Este producto lleva control de inventario? (stock null = ilimitado)
     */
    public function tracksStock(): bool
    {
        return $this->stock !== null;
    }

    /**
     * ¿Hay al menos $qty unidades disponibles?
     */
    public function inStock(int $qty = 1): bool
    {
        return ! $this->tracksStock() || $this->stock >= $qty;
    }

    /**
     * Unidades que aún se pueden agregar al pedir $requested (respeta el stock).
     */
    public function clampQuantity(int $requested): int
    {
        $requested = max(0, $requested);

        return $this->tracksStock() ? min($requested, max(0, (int) $this->stock)) : $requested;
    }

    // --- Variantes -----------------------------------------------------------
    // Estructura: [{label, price, stock}]. Si el producto tiene variantes, el
    // precio y el stock salen de la variante elegida (no del campo/columna base).

    public function hasVariants(): bool
    {
        return ! empty($this->variants);
    }

    /** @return array<int, array{label:string,price:float,stock:?int}> */
    public function variantList(): array
    {
        return collect(array_values($this->variants ?? []))
            ->map(fn ($v) => [
                'label' => $v['label'] ?? '',
                'price' => (float) ($v['price'] ?? 0),
                'stock' => (isset($v['stock']) && $v['stock'] !== null && $v['stock'] !== '') ? (int) $v['stock'] : null,
            ])
            ->all();
    }

    /** @return array{label:string,price:float,stock:?int}|null */
    public function variant(?int $index): ?array
    {
        if ($index === null) {
            return null;
        }

        return $this->variantList()[$index] ?? null;
    }

    public function variantPrice(int $index): float
    {
        return (float) ($this->variant($index)['price'] ?? 0);
    }

    /** null = esa variante no controla stock (ilimitada). */
    public function variantStock(int $index): ?int
    {
        $variant = $this->variant($index);

        return $variant ? $variant['stock'] : 0;
    }

    public function variantTracksStock(int $index): bool
    {
        return $this->variantStock($index) !== null;
    }

    public function variantInStock(int $index, int $qty = 1): bool
    {
        $stock = $this->variantStock($index);

        return $stock === null || $stock >= $qty;
    }

    /** ¿Todas las variantes están agotadas? */
    public function allVariantsSoldOut(): bool
    {
        if (! $this->hasVariants()) {
            return false;
        }

        foreach (array_keys($this->variantList()) as $i) {
            if ($this->variantInStock($i, 1)) {
                return false;
            }
        }

        return true;
    }

    /** @return array{0: float, 1: float} [precio mínimo, máximo] entre variantes. */
    public function variantPriceRange(): array
    {
        $prices = array_map(fn ($v) => $v['price'], $this->variantList());

        return $prices ? [min($prices), max($prices)] : [0.0, 0.0];
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entry extends Model
{
    use Publishable;

    protected $fillable = [
        'module_id', 'title', 'slug', 'data', 'stock', 'is_published', 'published_at', 'sort_order',
    ];

    protected $casts = [
        'data' => 'array',
        'stock' => 'integer',
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
}

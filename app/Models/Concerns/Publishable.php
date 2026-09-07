<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Contenido con borrador y publicación programada.
 * Visible = publicado Y (sin fecha programada O la fecha ya pasó).
 */
trait Publishable
{
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where(function (Builder $q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function isVisible(): bool
    {
        return (bool) $this->is_published
            && ($this->published_at === null || $this->published_at->lte(now()));
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entry extends Model
{
    use Publishable;

    protected $fillable = [
        'module_id', 'title', 'slug', 'data', 'is_published', 'published_at', 'sort_order',
    ];

    protected $casts = [
        'data' => 'array',
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
}

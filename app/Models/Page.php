<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use Publishable;

    protected $fillable = [
        'title', 'slug', 'content', 'is_published', 'published_at',
        'show_in_menu', 'sort_order', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'content' => 'array',
        'is_published' => 'boolean',
        'show_in_menu' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

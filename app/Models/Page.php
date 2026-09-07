<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title', 'slug', 'content', 'is_published',
        'show_in_menu', 'sort_order', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'content' => 'array',
        'is_published' => 'boolean',
        'show_in_menu' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

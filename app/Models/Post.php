<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasTranslations;
    use Publishable;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'cover_image', 'translations',
        'is_published', 'published_at', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'translations' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

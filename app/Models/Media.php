<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = ['name', 'path', 'disk', 'mime_type', 'size'];

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }

    public function getSizeForHumansAttribute(): string
    {
        $bytes = (int) $this->size;
        if ($bytes <= 0) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));
        $i = min($i, count($units) - 1);

        return round($bytes / (1024 ** $i), 1) . ' ' . $units[$i];
    }
}

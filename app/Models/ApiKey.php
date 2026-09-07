<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $fillable = ['name', 'token', 'last_used_at'];

    protected $casts = ['last_used_at' => 'datetime'];

    /**
     * Genera un token nuevo. Devuelve [tokenEnClaro, ApiKey].
     * El token en claro se muestra una sola vez; en la BD queda su hash.
     */
    public static function generate(string $name): array
    {
        $plain = 'cms_' . Str::random(40);

        $key = static::create([
            'name' => $name,
            'token' => hash('sha256', $plain),
        ]);

        return [$plain, $key];
    }
}

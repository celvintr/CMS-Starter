<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'service', 'starts_at', 'notes', 'status', 'source',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return ['pending' => 'Pendiente', 'confirmed' => 'Confirmada', 'canceled' => 'Cancelada'];
    }

    public function statusLabel(): string
    {
        return static::statuses()[$this->status] ?? $this->status;
    }
}

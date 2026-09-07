<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'reference', 'provider', 'customer_name', 'customer_email', 'customer_phone',
        'items', 'total', 'currency', 'status',
        'stripe_session_id', 'stripe_payment_intent',
        'paypal_order_id', 'paypal_capture_id', 'paid_at',
    ];

    protected $casts = [
        'items' => 'array',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}

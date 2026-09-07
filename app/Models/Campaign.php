<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campaign extends Model
{
    protected $fillable = ['mail_account_id', 'subject', 'body', 'status', 'recipients', 'sent_at'];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function mailAccount(): BelongsTo
    {
        return $this->belongsTo(MailAccount::class);
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }
}

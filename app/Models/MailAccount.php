<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailAccount extends Model
{
    protected $fillable = [
        'name', 'host', 'port', 'encryption',
        'username', 'password', 'from_address', 'from_name',
    ];

    protected $casts = [
        'password' => 'encrypted', // la contraseña SMTP se guarda encriptada
    ];
}

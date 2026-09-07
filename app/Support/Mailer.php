<?php

namespace App\Support;

use App\Models\MailAccount;
use Illuminate\Support\Facades\Mail;

class Mailer
{
    /**
     * Config de un mailer SMTP a partir de una cuenta.
     */
    public static function config(MailAccount $account): array
    {
        return [
            'transport' => 'smtp',
            'host' => $account->host,
            'port' => (int) $account->port,
            'encryption' => $account->encryption ?: null,
            'username' => $account->username,
            'password' => $account->password,
            'timeout' => 15,
        ];
    }

    /**
     * Envía un correo HTML usando la cuenta indicada.
     */
    public static function send(MailAccount $account, string $to, string $subject, string $html): void
    {
        config(['mail.mailers._dynamic' => static::config($account)]);

        Mail::mailer('_dynamic')->html($html, function ($message) use ($account, $to, $subject) {
            $message->to($to)->subject($subject);

            if ($account->from_address) {
                $message->from($account->from_address, $account->from_name ?: $account->name);
            }
        });
    }
}

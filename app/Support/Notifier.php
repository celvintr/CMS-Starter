<?php

namespace App\Support;

use App\Models\MailAccount;
use App\Models\Order;
use App\Models\SiteSetting;

/**
 * Envía notificaciones por correo según el propósito (formularios / órdenes),
 * usando la cuenta SMTP configurada para cada uno. Nunca rompe la petición.
 */
class Notifier
{
    /** Aviso de un mensaje de contacto o envío de formulario. */
    public static function forms(string $titulo, array $filas, ?string $origen = null): void
    {
        static::dispatch('forms', 'Nuevo: ' . $titulo, static::table($filas, $origen));
    }

    /** Aviso de una orden pagada. */
    public static function order(Order $order): void
    {
        $filas = [
            ['Referencia', $order->reference],
            ['Cliente', $order->customer_name],
            ['Correo', $order->customer_email],
            ['Teléfono', $order->customer_phone],
            ['Total', number_format((float) $order->total, 2) . ' ' . strtoupper($order->currency)],
            ['Método', ucfirst((string) $order->provider)],
        ];

        $items = '';
        foreach ((array) $order->items as $it) {
            $items .= '<li>' . e($it['qty'] ?? 1) . ' × ' . e($it['title'] ?? '') . '</li>';
        }

        $html = static::table($filas) . ($items ? "<h3>Productos</h3><ul>{$items}</ul>" : '');

        static::dispatch('orders', 'Orden pagada: ' . $order->reference, $html);
    }

    protected static function dispatch(string $channel, string $subject, string $html): void
    {
        try {
            $settings = SiteSetting::current();
            $accountId = $settings->{"notify_{$channel}_account_id"} ?? null;
            $to = $settings->{"notify_{$channel}_email"} ?? null;

            if (! $accountId || ! $to) {
                return; // canal no configurado
            }

            $account = MailAccount::find($accountId);
            if (! $account) {
                return;
            }

            Mailer::send($account, $to, $subject, $html);
        } catch (\Throwable $e) {
            // Un fallo de correo no debe romper el guardado del mensaje/orden.
        }
    }

    protected static function table(array $filas, ?string $origen = null): string
    {
        $rows = '';
        foreach ($filas as [$label, $value]) {
            if ($value === null || $value === '') {
                continue;
            }
            $rows .= '<tr><td style="padding:4px 12px 4px 0;color:#64748b">' . e($label) . '</td><td style="padding:4px 0"><strong>' . nl2br(e($value)) . '</strong></td></tr>';
        }

        $html = "<table style=\"border-collapse:collapse\">{$rows}</table>";

        if ($origen) {
            $html .= '<p style="color:#94a3b8;font-size:12px;margin-top:12px">Origen: ' . e($origen) . '</p>';
        }

        return $html;
    }
}

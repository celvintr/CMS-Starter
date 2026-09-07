<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Cliente mínimo de la Orders API v2 de PayPal (crear y capturar pagos).
 */
class PayPal
{
    public static function base(): string
    {
        return SiteSetting::current()->paypal_mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    protected static function token(): string
    {
        $settings = SiteSetting::current();

        $response = Http::asForm()
            ->withBasicAuth($settings->paypal_client_id, $settings->paypal_secret)
            ->timeout(30)
            ->post(self::base() . '/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        if (! $response->successful() || ! $response->json('access_token')) {
            throw new RuntimeException('PayPal: no se pudo autenticar (revisa las credenciales).');
        }

        return $response->json('access_token');
    }

    public static function createOrder(float $total, string $currency, string $reference, string $returnUrl, string $cancelUrl): array
    {
        $response = Http::withToken(self::token())
            ->timeout(30)
            ->post(self::base() . '/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $reference,
                    'amount' => [
                        'currency_code' => strtoupper($currency),
                        'value' => number_format($total, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                    'brand_name' => SiteSetting::current()->site_name,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('PayPal: no se pudo crear la orden.');
        }

        return $response->json();
    }

    public static function captureOrder(string $paypalOrderId): array
    {
        $response = Http::withToken(self::token())
            ->withBody('{}', 'application/json')
            ->timeout(30)
            ->post(self::base() . "/v2/checkout/orders/{$paypalOrderId}/capture");

        if (! $response->successful()) {
            throw new RuntimeException('PayPal: no se pudo capturar el pago.');
        }

        return $response->json();
    }

    public static function approveLink(array $order): ?string
    {
        foreach ($order['links'] ?? [] as $link) {
            if (($link['rel'] ?? null) === 'approve') {
                return $link['href'];
            }
        }

        return null;
    }
}

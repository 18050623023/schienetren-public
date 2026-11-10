<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Exception;

class PaymentManager
{
    public static function process(Payment $payment): array
    {
        $gateway = config('payment.default_gateway', 'manual');

        $service = self::resolveService($gateway);

        return $service->process($payment);
    }

    protected static function resolveService(string $gateway): PaymentGatewayInterface
    {
        return match ($gateway) {
            'manual' => new ManualTransferService(),
            'midtrans' => new MidtransService(),
            // Add other gateways here
            default => throw new Exception("Payment gateway [{$gateway}] is not supported."),
        };
    }
}

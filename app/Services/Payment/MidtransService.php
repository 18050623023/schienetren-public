<?php

namespace App\Services\Payment;

use App\Models\Payment;

class MidtransService implements PaymentGatewayInterface
{
    public function process(Payment $payment): array
    {
        // Placeholder for future Midtrans API integration
        // e.g., Call Midtrans Snap API to get snap token / redirect_url
        
        $payment->status = 'pending';
        $payment->save();

        return [
            'status' => 'pending',
            'message' => 'Mengarahkan ke halaman pembayaran...',
            'redirect_url' => '#', // To be replaced with actual Snap URL
        ];
    }
}

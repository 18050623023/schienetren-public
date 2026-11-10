<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Support\Str;

class ManualTransferService implements PaymentGatewayInterface
{
    public function process(Payment $payment): array
    {
        // Generate a unique reference number for manual transfer
        $payment->reference_number = 'MT-' . strtoupper(Str::random(10));
        $payment->status = 'pending';
        $payment->save();

        return [
            'status' => 'pending',
            'message' => 'Silakan lakukan transfer ke rekening yang tertera dan konfirmasi pembayaran Anda.',
            'reference_number' => $payment->reference_number,
        ];
    }
}

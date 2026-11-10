<?php

namespace App\Services\Payment;

use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Process a payment.
     *
     * @param Payment $payment
     * @return array Returns an array with 'status' (e.g. 'success', 'pending') and 'message' or 'redirect_url'
     */
    public function process(Payment $payment): array;
}

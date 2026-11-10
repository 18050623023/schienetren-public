<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default payment gateway that should be used
    | by the application.
    |
    | Supported: "manual", "midtrans", "xendit"
    |
    */

    'default_gateway' => env('PAYMENT_GATEWAY', 'manual'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configurations
    |--------------------------------------------------------------------------
    */

    'gateways' => [

        'manual' => [
            // Manual transfer settings if any
            'bank_name' => env('MANUAL_BANK_NAME', 'Bank BCA'),
            'account_number' => env('MANUAL_ACCOUNT_NUMBER', '1234567890'),
            'account_name' => env('MANUAL_ACCOUNT_NAME', 'PT Schienetren'),
        ],

        'midtrans' => [
            'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
            'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
            'server_key' => env('MIDTRANS_SERVER_KEY', ''),
            'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        ],

        'xendit' => [
            'secret_key' => env('XENDIT_SECRET_KEY', ''),
            'public_key' => env('XENDIT_PUBLIC_KEY', ''),
        ],
    ],
];

<?php

return [
    'openpay' => [
        'enabled' => (bool) env('OPENPAY_ENABLED', false),
        'sandbox' => (bool) env('OPENPAY_SANDBOX', true),
        'merchant_id' => env('OPENPAY_MERCHANT_ID'),
        'private_key' => env('OPENPAY_PRIVATE_KEY'),
        'public_key' => env('OPENPAY_PUBLIC_KEY'),
        'webhook_secret' => env('OPENPAY_WEBHOOK_SECRET'),
        'sandbox_url' => env('OPENPAY_SANDBOX_URL', 'https://sandbox-api.openpay.mx/v1'),
        'production_url' => env('OPENPAY_PRODUCTION_URL', 'https://api.openpay.mx/v1'),
    ],
];

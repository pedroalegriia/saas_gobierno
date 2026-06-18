<?php

return [
    'tenant_base_domain' => env('TENANT_BASE_DOMAIN', 'pagosmunicipales.com'),
    'capture_line_expiration_days' => (int) env('CAPTURE_LINE_EXPIRATION_DAYS', 15),
    'payment_default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'stripe'),
];

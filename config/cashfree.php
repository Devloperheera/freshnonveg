<?php

return [

    'app_id' => env('CASHFREE_APP_ID'),

    'secret_key' => env('CASHFREE_SECRET_KEY'),

    'environment' => strtoupper((string) env('CASHFREE_ENVIRONMENT', 'SANDBOX')),

    'api_version' => '2023-08-01',

    'api_base_url' => strtoupper((string) env('CASHFREE_ENVIRONMENT', 'SANDBOX')) === 'PRODUCTION'
        ? 'https://api.cashfree.com'
        : 'https://api-sandbox.cashfree.com',

    'checkout_base_url' => strtoupper((string) env('CASHFREE_ENVIRONMENT', 'SANDBOX')) === 'PRODUCTION'
        ? 'https://payments.cashfree.com'
        : 'https://sandbox.cashfree.com',

];

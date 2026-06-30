<?php

return [
    'secret_key' => env('PAYSTACK_SECRET_KEY'),
    'public_key' => env('PAYSTACK_PUBLIC_KEY'),
    'webhook_secret' => env('PAYSTACK_WEBHOOK_SECRET'),
    'base_url' => 'https://api.paystack.co',
];

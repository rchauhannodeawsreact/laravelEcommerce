<?php

return [
    'merchant_id' => env('PAYTM_MERCHANT_ID', ''),
    'merchant_key' => env('PAYTM_MERCHANT_KEY', ''),
    'merchant_website' => env('PAYTM_MERCHANT_WEBSITE', 'WEBSTAGING'),
    'channel_id' => env('PAYTM_CHANNEL_ID', 'WEB'),
    'industry_type_id' => env('PAYTM_INDUSTRY_TYPE_ID', 'Retail'),
    'callback_url' => env('PAYTM_CALLBACK_URL', 'http://localhost:8000/api/payment/callback'),
    'transaction_url' => env('PAYTM_TRANSACTION_URL', 'https://securegw-stage.paytm.in/order/initiate'),
];

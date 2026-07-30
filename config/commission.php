<?php

return [
    'default_percentage' => env('COMMISSION_DEFAULT_PERCENTAGE', 10),
    'min_settlement_amount' => env('COMMISSION_MIN_SETTLEMENT', 1000),
    'settlement_frequency' => env('COMMISSION_SETTLEMENT_FREQUENCY', 'monthly'), // daily, weekly, monthly
    'platform_fee_percentage' => env('COMMISSION_PLATFORM_FEE', 0),
    'auto_approve' => env('COMMISSION_AUTO_APPROVE', false),
];

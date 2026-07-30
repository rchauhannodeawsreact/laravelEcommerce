<?php

return [
    'purchase_coin_rate' => env('COIN_PURCHASE_RATE', 1), // coins per 100 rupees
    'referrer_reward_coins' => env('COIN_REFERRER_REWARD', 500),
    'referred_reward_coins' => env('COIN_REFERRED_REWARD', 250),
    'conversion_rate' => env('COIN_CONVERSION_RATE', 0.5), // rupees per coin
    'min_redemption_amount' => env('COIN_MIN_REDEMPTION', 10),
    'expiry_days' => env('COIN_EXPIRY_DAYS', null), // null = no expiry
    'max_transfer_amount' => env('COIN_MAX_TRANSFER', 10000),
];

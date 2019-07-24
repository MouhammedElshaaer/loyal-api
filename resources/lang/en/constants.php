
<?php

return [

    'default_locale' => 'en',

    'settings' =>[
        'pending_duration' => 'PENDING_DURATION',
        'premium_pending_duration' => 'PREMIUM_PENDING_DURATION',
        'valid_duration' => 'VALID_DURATION',
        'premium_valid_duration' => 'PREMIUM_VALID_DURATION',
        'points_per_currency_unit' => 'POINTS_PER_CURRENCY_UNIT',
        'premium_points_per_currency_unit' => 'PREMIUM_POINTS_PER_CURRENCY_UNIT',
        'currency_unit' => 'CURRENCY_UNIT',
        'premium_currency_unit' => 'PREMIUM_CURRENCY_UNIT',
        'premium_threshold' => 'PREMIUM_THRESHOLD',
        'premium_allowed' => 'PREMIUM_ALLOWED',
        'policies' => 'POLICIES',
        'ads' => 'ADS',
    ],

    'default_settings' =>[
        "valid_duration" => "5",
        "premium_valid_duration" => "7",
        "pending_duration" => "1",
        "premium_pending_duration" => "0",
        "points_per_currency_unit" => "4",
        "premium_points_per_currency_unit" => "8",
        "currency_unit" => "1",
        "premium_currency_unit" => "1",
        "premium_threshold" => "1000",
        "premium_allowed" => false,
        "policies" => "new may be simple text or html markup"
    ],

    'roles' =>[
        'admin' => 'admin',
        'customer' => 'customer',
        'cashier' => 'cashier',
        'premium' => 'premium',
    ],

    'status' =>[
        'valid_status' => '100',
        'used_status' => '110',
        'refunded_status' => '111',
        'pending_status' => '10',
        'expired_status' => '11',
        'status_error' => '0',
    ],
    
];

<?php

return [

    'default_locale' => 'en',

    'settings' => [
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

    'default_settings' => [
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

    'roles' => [
        'admin' => 'admin',
        'customer' => 'customer',
        'cashier' => 'cashier',
        'premium' => 'premium',
    ],

    'status_codes' => [
        'valid_status' => '100',
        'used_status' => '110',
        'refunded_status' => '111',
        'pending_status' => '10',
        'expired_status' => '11',
        'status_error' => '0',
    ],

    'status' => [
        '100' => 'valid',
        '110' => 'used',
        '111' => 'refunded',
        '10' => 'pending',
        '11' => 'expired',
        '0' => 'status error',
    ],

    'scopes' => [
        'customer' => 'CUSTOMER',
        'cashier' => 'CASHIER',
        'admin' => 'ADMIN',
        'system' => 'SYSTEM',
    ],

    'actions' => [
        'transaction_refund_success' => 'TRANSACTION_REFUND',
        'transaction_add_transaction_success' => 'TRANSACTION_ADD',
        'transaction_voucher_used_success' => 'TRANSACTION_ADD_WITH_VOUCHER',
        'voucher_instance_check_valid' => 'VOUCHER_CHECK_VALID',
        'voucher_instance_check_used' => 'VOUCHER_CHECK_USED',
        'voucher_instance_check_expired' => 'VOUCHER_CHECK_EXPIRED',
        'voucher_redeem_success' => 'REDEEM',
    ],

    'file_uploading' => [
        'image_size_threshold_mb' => '1',
        'image_thumbnail_suffix' => 'thumbnail',
        'image_thumbnail_width_px' => '512',
        'image_storage_path' => 'storage/images/',
    ],

    'testing' => [
        'users_number' => '100',
        'transactions_number' => '100',
        'vouchers_number' => '100',
        'voucher_instances_number' => '100',
        'invoice_max_value' => '1000',
        'voucher_min_points' => '100',
        'voucher_max_points' => '5000',
        'points_min_value' => '4',
        'points_max_value' => '4000',
        'min_discount' => '10',
        'max_discount' => '50',
        'date_start' => '-15 days',
        'date_end' => '+15 days',
    ],
];

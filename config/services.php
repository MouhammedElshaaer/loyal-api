<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'google' => [
        // 'client_id' => '485802115186-l7hmv3ube5b7vuue8vdprdlsdt4j6htm.apps.googleusercontent.com', //loyal.['loyal']
        // 'client_secret' => 'Gvb_ow0o26Coh4c9cOg5AopE',//loyal.['loyal']
        'client_id' => '449221771045-9u290n0csjj3fu3ftfdglm546lqovp16.apps.googleusercontent.com', //loyality.['Web client 1']
        'client_secret' => 'gRqUaiW8YAV0aYvwLIbjXTr7',//loyality['Web client 1']
        'redirect' => "",
    ],

    'facebook' => [
        'client_id' => "878316139200558",
        'client_secret' => "8f8b0df7d7fdeebc2ac715aea66d2261",
        'redirect' => "",
    ],

];

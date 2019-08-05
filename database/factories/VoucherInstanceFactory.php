<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\VoucherInstance;
use Faker\Generator as Faker;

$factory->define(VoucherInstance::class, function (Faker $faker) {
    return [
        'voucher_id' => $faker->numberBetween($min = 1, $max = intval(config('constants.testing.vouchers_number'))),
        'user_id' => $faker->numberBetween($min = 1, $max = intval(config('constants.testing.users_number'))),
        'qr_code' => $faker->unique()->uuid,
    ];
});

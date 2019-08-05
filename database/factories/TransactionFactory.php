<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Transaction;
use Faker\Generator as Faker;

$factory->define(Transaction::class, function (Faker $faker) {
    return [
        'user_id' => $faker->numberBetween($min = 1, $max = intval(config('constants.testing.users_number'))),
        'invoice_number' => $faker->unique()->numberBetween($min = 1, $max = intval(config('constants.testing.transactions_number'))),
        'invoice_value' => $faker->numberBetween($min = 5, $max = intval(config('constants.testing.invoice_max_value'))),
        'created_at' => \Carbon\Carbon::parse($faker->dateTimeBetween($startDate = config('constants.testing.date_start'), $endDate = config('constants.testing.date_end')))
    ];
});
